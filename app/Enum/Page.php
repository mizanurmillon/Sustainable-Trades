<?php

namespace App\Enum;

enum Page : string
{
    case TUTORIALS = 'tutorials';
    case FAQ = 'faq';
    case CONTACT = 'contact';
    case TERMS_AND_CONDITIONS = 'terms_and_conditions';
    case INFRINGEMENT_REPORT = 'infringement_report';
}
