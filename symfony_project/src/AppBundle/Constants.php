<?php

namespace AppBundle;

class Constants
{
    /* Roles */
    const HELPS_ROLE = "Helps";
    const JUDGES_ROLE = "Judges";
    const TAKES_ROLE = "Takes";
    const TEACHES_ROLE = "Teaches";
    public static $ROLE_NAMES = [
        Constants::HELPS_ROLE,
        Constants::JUDGES_ROLE,
        Constants::TAKES_ROLE,
        Constants::TEACHES_ROLE
    ];

    const SUPER_ROLE = "ROLE_SUPER";
    const ADMIN_ROLE = "ROLE_ADMIN";

    public static $ROLES = [
        CONSTANTS::ADMIN_ROLE,
        CONSTANTS::SUPER_ROLE
    ];

    const LONG_RESPONSE_LEVEL = "Long";
    const NONE_RESPONSE_LEVEL = "None";
    const SHORT_RESPONSE_LEVEL = "Short";

    const BOTH_TESTCASE_OUTPUT_LEVEL = "Both";
    const NONE_TESTCASE_OUTPUT_LEVEL = "None";
    const OUTPUT_TESTCASE_OUTPUT_LEVEL = "Output";

    const SCOREBOARD_FREEZE_ACTION = "freeze";
    const SCOREBOARD_UNFREEZE_ACTION = "unfreeze";

    const SUBMISSION_JUDGING_WRONG = "wrong";
    const SUBMISSION_JUDGING_CORRECT = "correct";
    const SUBMISSION_JUDGING_DELETE = "delete";
    const SUBMISSION_JUDGING_FORMATTING = "formatting";
    const SUBMISSION_JUDGING_MESSAGE = "message";
    const SUBMISSION_JUDGING_CLAIMED = "claimed";
    const SUBMISSION_JUDGING_UNCLAIMED = "unclaimed";
}

?>