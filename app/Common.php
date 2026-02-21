<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('t')) {
    function t(string $key, array $args = []): string
    {
        return lang('App.' . $key, $args);
    }
}

if (! function_exists('status_label')) {
    function status_label(string $status): string
    {
        $map = [
            'Received' => 'status_received',
            'Diagnosis' => 'status_diagnosis',
            'WaitingParts' => 'status_waiting_parts',
            'RepairInProgress' => 'status_repair_in_progress',
            'Testing' => 'status_testing',
            'ReadyToShip' => 'status_ready_to_ship',
            'Shipped' => 'status_shipped',
            'Closed' => 'status_closed',
            'Cancelled' => 'status_cancelled',
        ];

        return isset($map[$status]) ? t($map[$status]) : $status;
    }
}

if (! function_exists('priority_label')) {
    function priority_label(string $priority): string
    {
        $map = [
            'Low' => 'priority_low',
            'Medium' => 'priority_medium',
            'High' => 'priority_high',
            'Critical' => 'priority_critical',
        ];

        return isset($map[$priority]) ? t($map[$priority]) : $priority;
    }
}

if (! function_exists('yes_no_label')) {
    function yes_no_label(bool $value): string
    {
        return $value ? t('yes') : t('no');
    }
}
