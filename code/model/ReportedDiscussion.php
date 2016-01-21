<?php

class ReportedDiscussion extends DataObject
{
    private static $has_one = array(
        "Reported" => "Discussion",
        "Reporter" => "Member"
    );
}
