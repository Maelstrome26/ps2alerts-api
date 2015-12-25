<?php

namespace Ps2alerts\Api\Validator;

class AlertInputValidator
{
    /**
     * Validates POST input
     *
     * @param  string $type   Type of variable to check
     * @param  string $value
     *
     * @return boolean
     */
    public function validatePostVars($type, $value)
    {
        if ($type === 'ResultServer') {
            $value = intval($value); // Convert to integer if needed
            switch ($value) {
                case 1:
                case 10:
                case 13:
                case 17:
                case 19:
                case 25:
                    return true;
            }
        }

        if ($type === 'ResultWinner') {
            $value = strtoupper($value);
            switch ($value) {
                case 'VS':
                case 'NC':
                case 'TR':
                case 'DRAW':
                    return true;
                    break;
            }
        }

        if ($type === 'ResultAlertCont') {
            $value = intval($value);
            switch ($value) {
                case 2:
                case 4:
                case 6:
                case 8:
                    return true;
                    break;
            }
        }

        if ($type === 'ResultDomination') {
            $value = intval($value);
            switch ($value) {
                case 0:
                case 1:
                    return true;
                    break;
            }
        }

        return false;
    }
}
