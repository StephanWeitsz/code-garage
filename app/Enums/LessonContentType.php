<?php

namespace App\Enums;

enum LessonContentType: string
{
    case Text = 'text';
    case Markdown = 'markdown';
    case Video = 'video';
    case Code = 'code';
    case Image = 'image';
}
