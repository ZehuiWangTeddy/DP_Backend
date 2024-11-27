<?php

namespace App\Helpers;

class ViewingClassificationHelper
{
    /**
     * Determine the viewing classification based on age restriction.
     *
     * @param int $age
     * @return string
     */
    public static function determineViewingClassification(int $age): string
    {
        switch ($age) {
            case 0:
                return 'All Ages';
            case 6:
                return '6+';
            case 9:
                return '9+';
            case 12:
                return '12+';
            case 16:
                return '16+';
            case 18:
                return '18+';
            default:
                return 'All Ages';
        }
    }
}
