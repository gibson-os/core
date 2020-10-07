<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTime;
use DateTimeInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Model\Event\Trigger;
use GibsonOS\Core\Utility\JsonUtility;
use mysqlTable;
use stdClass;

class EventRepository extends AbstractRepository
{
    /**
     * @var JsonUtility
     */
    private $jsonUtility;

    public function __construct(JsonUtility $jsonUtility)
    {
        $this->jsonUtility = $jsonUtility;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getById(int $id): Event
    {
        $table = $this->getTable(Event::getTableName());
        $table
            ->setWhere('`id`=?')
            ->addWhereParameter($id)
            ->setLimit(1)
        ;

        if (!$table->selectPrepared()) {
            $exception = new SelectError('Event not found!');
            $exception->setTable($table);

            throw $exception;
        }

        $model = new Event();
        $model->loadFromMysqlTable($table);

        return $model;
    }

    /**
     * @return Event[]
     */
    public function getTimeControlled(DateTimeInterface $dateTime): array
    {
        $table = $this->initializeTable();
        $table->setWhere(
            '`event_trigger`.`trigger`=' . $this->escape(Trigger::TRIGGER_CRON) . ' AND ' .
            '(`event_trigger`.`weekday` IS NULL OR `event_trigger`.`weekday`=' . (int) $dateTime->format('w') . ') AND ' .
            '(`event_trigger`.`day` IS NULL OR `event_trigger`.`day`=' . (int) $dateTime->format('j') . ') AND ' .
            '(`event_trigger`.`month` IS NULL OR `event_trigger`.`month`=' . (int) $dateTime->format('n') . ') AND ' .
            '(`event_trigger`.`year` IS NULL OR `event_trigger`.`year`=' . (int) $dateTime->format('Y') . ') AND ' .
            '(`event_trigger`.`hour` IS NULL OR `event_trigger`.`hour`=' . (int) $dateTime->format('H') . ') AND ' .
            '(`event_trigger`.`minute` IS NULL OR `event_trigger`.`minute`=' . (int) $dateTime->format('m') . ') AND ' .
            '(`event_trigger`.`second` IS NULL OR `event_trigger`.`second`=' . (int) $dateTime->format('s') . ')'
        );

        if (!$table->select(false)) {
            return [];
        }

        return $this->matchModels($table->connection->fetchObjectList());
    }

    /**
     * @return mysqlTable
     */
    private function initializeTable()
    {
        $table = $this->getTable(Element::getTableName());
        $table->appendJoinLeft('`event`', '`event_element`.`event_id`=`event`.`id`');
        $table->appendJoinLeft('`event_trigger`', '`event_element`.`event_id`=`event_trigger`.`event_id`');
        $table->setOrderBy('`event_trigger`.`priority` DESC, `event_element`.`parent_id`, `event_element`.`order`');
        $table->setSelectString(
            '`event`.`id`, ' .
            '`event`.`name`, ' .
            '`event`.`active`, ' .
            '`event`.`async`, ' .
            '`event`.`modified`, ' .
            '`event_element`.`id` AS `elementId`, ' .
            '`event_element`.`parent_id` AS `elementParentId`, ' .
            '`event_element`.`order` AS `elementOrder`, ' .
            '`event_element`.`class` AS `elementClass`, ' .
            '`event_element`.`method` AS `elementMethod`, ' .
            '`event_element`.`parameters` AS `elementParameters`, ' .
            '`event_element`.`command` AS `elementCommand`, ' .
            '`event_element`.`operator` AS `elementOperator`, ' .
            '`event_element`.`returns` AS `elementReturns`, ' .
            '`event_trigger`.`id` AS `triggerId`, ' .
            '`event_trigger`.`trigger` AS `triggerTrigger`, ' .
            '`event_trigger`.`weekday` AS `triggerWeekday`, ' .
            '`event_trigger`.`day` AS `triggerDay`, ' .
            '`event_trigger`.`month` AS `triggerMonth`, ' .
            '`event_trigger`.`year` AS `triggerYear`, ' .
            '`event_trigger`.`hour` AS `triggerHour`, ' .
            '`event_trigger`.`minute` AS `triggerMinute`, ' .
            '`event_trigger`.`priority` AS `triggerPriority`'
        );

        return $table;
    }

    /**
     * @throws DateTimeError
     * @throws SaveError
     */
    public function saveElements(Event $event, array $elements, int $parentId = null): void
    {
        $order = 0;

        foreach ($elements as $element) {
            $elementModel = (new Event\Element())
                ->setEvent($event)
                ->setParentId($parentId)
                ->setClass($element['className'])
                ->setMethod($element['method'])
                ->setCommand($element['command'] ?: null)
                ->setOperator($element['operator'] ?: null)
                ->setParameters(
                    empty($element['parameters']) ? null : $this->jsonUtility->encode($element['parameters'])
                )
                ->setReturns(empty($element['returns']) ? null : $this->jsonUtility->encode($element['returns']))
                ->setOrder($order++)
            ;
            $elementModel->save();
            $this->saveElements($event, $element['children'], $elementModel->getId());
        }
    }

    /**
     * @param stdClass[] $events
     *
     * @return Event[]
     */
    private function matchModels($events)
    {
        /**
         * @var Event[]
         */
        $models = [];
        $triggerModels = [];
        $elementModels = [];

        foreach ($events as $event) {
            if (!isset($models[$event->id])) {
                $models[$event->id] = (new Event())
                    ->setId((int) $event->id)
                    ->setName($event->name)
                    ->setActive((bool) $event->active)
                    ->setAsync((bool) $event->async)
                    ->setModified(new DateTime($event->modified))
                ;
            }

            if (!isset($triggerModels[$event->triggerId])) {
                $triggerModel = (new Trigger())
                    ->setId((int) $event->triggerId)
                    ->setEvent($models[$event->id])
                    ->setWeekday((int) $event->triggerWeekday ?: null)
                    ->setDay((int) $event->triggerDay ?: null)
                    ->setMonth((int) $event->triggerMonth ?: null)
                    ->setYear((int) $event->triggerYear ?: null)
                    ->setHour((int) $event->triggerHour ?: null)
                    ->setMinute((int) $event->triggerMinute ?: null)
                    ->setPriority((int) $event->triggerPriority ?: null)
                ;
                $models[$event->id]->addTrigger($triggerModel);
                $triggerModels[$event->triggerId] = $triggerModel;
            }

            $elementModel = (new Element())
                ->setId((int) $event->elementId)
                ->setEvent($models[$event->id])
                ->setParent($event->elementParentId === null ? null : $elementModels[$event->elementParentId])
                ->setOrder($event->elementOrder)
                ->setClass($event->elementClass)
                ->setMethod($event->elementMethod)
                ->setParameters($event->elementParameters)
                ->setCommand($event->elementCommand)
                ->setOperator($event->elementOperator)
                ->setReturns($event->elementReturns)
            ;
            $models[$event->id]->addElement($elementModel);
            $elementModels[$event->elementId] = $elementModel;
        }

        return $models;
    }
}
