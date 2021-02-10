<?php

namespace App\Core\Mail;

use App\Models\Model;

class MailAttachment extends Model {

    const TBNAME = 'mail_attachments';
    const DISPOSITION_CONTENT_INLINE = 'inline';
    const DISPOSITION_CONTENT_ATTACHMENT = 'attachment';

    public static $columns = [
        'mail_queue_id',
        'content',
        'filename',
        'mimetype',
        'disposition',
    ];

    /** @var int */
    var $mail_queue_id = 0;

    /** @var string */
    var $content;

    /** @var string */
    var $filename = '';

    /** @var string */
    var $mimetype = '';

    /** @var string */
    var $disposition = self::DISPOSITION_CONTENT_ATTACHMENT;

}
