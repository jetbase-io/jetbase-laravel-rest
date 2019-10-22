<?php

function validRFC3339Date($date)
{
    if (DateTime::createFromFormat(DateTime::RFC3339, $date) === false) {
        return false;
    } else {
        return true;
    }
}