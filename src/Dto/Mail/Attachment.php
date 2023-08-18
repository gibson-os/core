<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Mail;

use GibsonOS\Core\Enum\Mail\Disposition;

class Attachment
{
    public function __construct(
        private string $filename,
        private string $content,
        private Disposition $disposition = Disposition::ATTACHMENT,
    ) {
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): Attachment
    {
        $this->filename = $filename;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): Attachment
    {
        $this->content = $content;

        return $this;
    }

    public function getDisposition(): Disposition
    {
        return $this->disposition;
    }

    public function setDisposition(Disposition $disposition): Attachment
    {
        $this->disposition = $disposition;

        return $this;
    }
}
