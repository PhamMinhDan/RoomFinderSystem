<?php

namespace Models;

class Message extends BaseModel
{
    protected static string $table      = 'chat_messages';
    protected static string $primaryKey = 'message_id';

    const TYPE_TEXT   = 'text';
    const TYPE_IMAGE  = 'image';
    const TYPE_FILE   = 'file';
    const TYPE_SYSTEM = 'system';
}