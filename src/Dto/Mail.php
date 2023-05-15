<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

use GibsonOS\Core\Dto\Mail\Address;
use GibsonOS\Core\Enum\SmtpEncryption;

class Mail
{
    /**
     * @param Address[] $to
     * @param Address[] $cc
     * @param Address[] $bcc
     */
    public function __construct(
        private string $subject,
        private string $html,
        private string $plain,
        private Address $from,
        private array $to,
        private string $host,
        private int $port,
        private string $username,
        private string $password,
        private ?SmtpEncryption $encryption = null,
        private array $cc = [],
        private array $bcc = [],
        private ?Address $replyTo = null,
    ) {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): Mail
    {
        $this->subject = $subject;

        return $this;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function setHtml(string $html): Mail
    {
        $this->html = $html;

        return $this;
    }

    public function getPlain(): string
    {
        return $this->plain;
    }

    public function setPlain(string $plain): Mail
    {
        $this->plain = $plain;

        return $this;
    }

    public function getFrom(): Address
    {
        return $this->from;
    }

    public function setFrom(Address $from): Mail
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): array
    {
        return $this->to;
    }

    public function setTo(array $to): Mail
    {
        $this->to = $to;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): Mail
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): Mail
    {
        $this->port = $port;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): Mail
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): Mail
    {
        $this->password = $password;

        return $this;
    }

    public function getEncryption(): ?SmtpEncryption
    {
        return $this->encryption;
    }

    public function setEncryption(?SmtpEncryption $encryption): Mail
    {
        $this->encryption = $encryption;

        return $this;
    }

    public function getCc(): array
    {
        return $this->cc;
    }

    public function setCc(array $cc): Mail
    {
        $this->cc = $cc;

        return $this;
    }

    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function setBcc(array $bcc): Mail
    {
        $this->bcc = $bcc;

        return $this;
    }

    public function getReplyTo(): ?Address
    {
        return $this->replyTo;
    }

    public function setReplyTo(?Address $replyTo): Mail
    {
        $this->replyTo = $replyTo;

        return $this;
    }
}
