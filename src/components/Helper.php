<?php

namespace bizley\podium\components;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use bizley\podium\models\User;

class Helper
{
    public static function sortOrder($attribute = null)
    {
        if (!empty($attribute)) {
            $sort = Yii::$app->request->get('sort');
            if ($sort == $attribute) {
                return ' ' . Html::tag('span', '', ['class' => 'glyphicon glyphicon-sort-by-alphabet']);
            }
            elseif ($sort == '-' . $attribute) {
                return ' ' . Html::tag('span', '', ['class' => 'glyphicon glyphicon-sort-by-alphabet-alt']);
            }
        }
        
        return null;
    }
    
    public static function roleLabel($role = null)
    {
        switch ($role) {
            case User::ROLE_ADMIN:
                $label = 'danger';
                $name = ArrayHelper::getValue(User::getRoles(), $role);
                break;
            case User::ROLE_MODERATOR:
                $label = 'primary';
                $name = ArrayHelper::getValue(User::getRoles(), $role);
                break;
            default:
                $label = 'success';
                $name = ArrayHelper::getValue(User::getRoles(), User::ROLE_MEMBER);
        }
        
        return Html::tag('span', Yii::t('podium/view', $name), ['class' => 'label label-' . $label]);
    }
    
    public static function statusLabel($status = null)
    {
        switch ($status) {
            case User::STATUS_ACTIVE:
                $label = 'info';
                $name = ArrayHelper::getValue(User::getStatuses(), $status);
                break;
            case User::STATUS_BANNED:
                $label = 'warning';
                $name = ArrayHelper::getValue(User::getStatuses(), $status);
                break;
            default:
                $label = 'default';
                $name = ArrayHelper::getValue(User::getStatuses(), User::STATUS_REGISTERED);
        }
        
        return Html::tag('span', Yii::t('podium/view', $name), ['class' => 'label label-' . $label]);
    }
    
    public static function defaultAvatar()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPoAAAD6CAMAAAC/MqoPAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkQ2QUM0OTFBRDMyRTExRTQ4QjFBQzhBOUQ0OEJENjQ5IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkQ2QUM0OTFCRDMyRTExRTQ4QjFBQzhBOUQ0OEJENjQ5Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6RDZBQzQ5MThEMzJFMTFFNDhCMUFDOEE5RDQ4QkQ2NDkiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6RDZBQzQ5MTlEMzJFMTFFNDhCMUFDOEE5RDQ4QkQ2NDkiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7mye3vAAAAk1BMVEXPz8/////4+PjX19f39/fx8fHp6enT09Pd3d3V1dXZ2dno6Oj5+fnc3Nzs7Oz+/v7k5OTl5eX09PTz8/Pw8PDy8vLn5+fU1NTt7e3b29ve3t7R0dHu7u7j4+P7+/vh4eH29vbf39/S0tLg4OD6+vrW1tbq6ur9/f3i4uL8/Pza2trv7+/Y2Nj19fXm5ubr6+vQ0NDQWjtKAAAFDUlEQVR42uyd2XLiSgxAkfEG2BhvLGEzCYRtksz/f93cp1s1VVM1Q6xWS0jnD07ZVqvVankwMAzDMAzDMAzDMAzDMAzDMAzDMAzDMAzDMAzDMAzDMAzDMB7nlM7iTR0ezwUU52NYb+JZenp669e83ITwJ8JNmb8+rfdoP/2z9v/60/3oGZ/3YZrB38mmhyd79sOygn+lKofPIz6eBPAIwWT8JE98ksGjZJMnePLJrIPv0M0S4eYvEXyX6EX0cjbZwffZTeQudWkE/YhSoebbAPoSbEXGt7iA/hSxvGg3XAIOS2nL3LgBLBpZ+c2qBTzalSTzGjCp5biPW8CllfLODxvAppER65Il4LMUscbF4IJYQg5XOFEv+Od1aQBuCLjn86MIXBEx38dNwB0T3pWJnUP1HefaRRKBSyLGK9wM3DLjm8Z1jtW7ocYYxzvSjTPn6tlY60Pn+tiHAYF6wPJrL4GCkqH5a0WiXjE8gz4ADQd+6lMi9Sm/LVtGpJ6x28DtgYq91ved3xv/GpKph8xifA505BrzGZZZzYZQfcNLPSRUD1mZn4ASVr3EKak6q4r8jFSdVYkuJlWPtQZ4ZiG+JlWvta5tzFa3I6n6kZP6mVT9zEm9IFUvOKkDLfbU7Vu3CG/rumVzlsPbzs3261alsdqcVWStDm+nL3bm9gCKT1oVn69r7qpQ3EujuINqcCBSZ9g3p7hbUnGPrObOaMX98JpvQSi++6L5xpPie26abzcqvtOq+Saz5vvriqcWaJ5VoXlCiea5NIqnEWmeQaV58pjmeXOapwxqni2peaLoQPEc2YHm6cGaZ0YPek0Kb0VPCh8MfsTf7hwO5z8Ei9/iXqX5LL4JFR/HvXvFj7HEv0KM5ij1mmAuLdb9vKCdOVeXn5LMV6gthBs5+5fkE/ngMfsUks7m+FUaaHIB4uvSyVlzVq7Z5zDOOqmmzDOcF4cXgGrWme3MaWNFxveQOXF++4dr4WK4BOfwLFfdIiAgYrijSWm6JaFiV7k5dEBEx6xL9o3wylP4xso8AEKCN63mnNwPxOb/uTP53tMOyOlYxPlVBR6oGJQv0PsIxLQbjO7gibvneuX6C7zx5bd4MQePzH2aX64+1a8XjwXIELwSeitWniLwTORrdsM7eOfdUyEOGOClXJcHHNQDD5/7ogEWNAtdK7rX1d3pBZ/Huo2ITyYWEbAhWuh83clf+fzISf1IGOXXd2DFnW4PtwVmkLXSDj+4qX9QHcXFwA6iu2D5jp/6jibSLYEhS5KjFmAJwYHMuuGp3rhf4C7AFOeFuqTlqt4m2rIZsrwmqfmq14nOL935175uOau3LoP8Hljjcuxkw1u9cdg/Acxx12sx5a7ubObmeMddfeeq1WIO7HFUoVx0/NW7hcaVzeX6dpegfndhfjtLUD/fdAY5R4FuXclQr/AT+RcQAv7B65cU9S/0RT2Uoh5iL+1vIAbssvRUjjryHmYRyFEPcN/4AwgC93bIuyR11BbKdSdJvcPManIQRa4vf3eRx0ey1CM889NVlvr1pDGVw07o3qWp4y1vlTT1Cq1PrpCmXmD10e1BHHutnzrex17LU0f6T/niKk/9irNxTUEgOMfNpUR1nF/eLSWq4/TMfkhU/0CJcmeJ6meMOJeDSDDKFVuZ6luduRxWPhfJVMeo1AQy1YP+5qNCpnrRf2zNCoTSf1LTXqp6/y37p1T1T02ny7/zt7PmXwIMAMb4j9DtABgnAAAAAElFTkSuQmCC';
    }
    
    public static function podiumPurifierConfig()
    {
        return ['HTML.Allowed' => 'img[src|style|class|alt],a[href|target],br,p,span[style],hr,ul,ol,li', 'Attr.AllowedFrameTargets' => ['_blank']];
    }
    
    public static function deletedUserTag($simple = false)
    {
        return self::podiumUserTag('', 0, null, $simple);
    }
    
    public static function podiumUserTag($name, $role, $id = null, $simple = false)
    {
        $icon = Html::tag('span', '', ['class' => $id ? 'glyphicon glyphicon-user' : 'glyphicon glyphicon-ban-circle']);
        $url = $id ? ['members/view', 'id' => $id] : '#';
        switch ($role) {
            case 0:
                $colourClass = 'text-muted';
                break;
            case User::ROLE_MODERATOR:
                $colourClass = 'text-primary';
                break;
            case User::ROLE_ADMIN:
                $colourClass = 'text-danger';
                break;
            case User::ROLE_MEMBER:
            default:
                $colourClass = 'text-success';
        }
        $encodedName = Html::tag('span', $icon . ' ' . ($id ? Html::encode($name) : Yii::t('podium/view', 'user deleted')), ['class' => $colourClass]);
        
        if ($simple) {
            return $encodedName;
        }
        
        return Html::a($encodedName, $url, ['class' => 'btn btn-xs btn-default']);
    }
    
    public static function replyBgd()
    {
        return 'style="background-repeat:repeat-y;background-position:top right;background-image:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAUCAYAAAB7wJiVAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkI2NTVERkRDREEyRTExRTRCRkI5OEQyMTc5QURDMkNDIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkI2NTVERkREREEyRTExRTRCRkI5OEQyMTc5QURDMkNDIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6QjY1NURGREFEQTJFMTFFNEJGQjk4RDIxNzlBREMyQ0MiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6QjY1NURGREJEQTJFMTFFNEJGQjk4RDIxNzlBREMyQ0MiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6k5fCuAAAAyklEQVR42uyXsQ2DMBBFIcooLnCVBajcpaXOJqnZhDojULEDltxkE3KWLpKVAtxwSvGe9EXBr/4TSNemlBqllwySIPGSVTJLXpIlF5xzDZzLVZ8PyVPSFe9umrtklEzMdT4X/TJ+ZZR0+r5nLhshw46MUsrAXDZCQmU3MJeNEF/Z9cxlI2St7K7MZSNkruzOzGUjJN8Z8aAXtQcGQha9M+KOjPF7HILNYZiPvvfRpQ7n027bxgp/9ssChABCEAIIQQggBCFgyUeAAQBSzyA8dwka9AAAAABJRU5ErkJggg==\');"';
    }
    
    public static function timeZones()
    {
        return [
            'UTC' => Yii::t('podium/view', 'default (UTC)'),
            Yii::t('podium/view', 'Africa') => [
                'Africa/Abidjan' => 'Africa/Abidjan',
                'Africa/Accra' => 'Africa/Accra',
                'Africa/Addis_Ababa' => 'Africa/Addis_Ababa',
                'Africa/Algiers' => 'Africa/Algiers',
                'Africa/Asmara' => 'Africa/Asmara',
                'Africa/Asmera' => 'Africa/Asmera',
                'Africa/Bamako' => 'Africa/Bamako',
                'Africa/Bangui' => 'Africa/Bangui',
                'Africa/Banjul' => 'Africa/Banjul',
                'Africa/Bissau' => 'Africa/Bissau',
                'Africa/Blantyre' => 'Africa/Blantyre',
                'Africa/Brazzaville' => 'Africa/Brazzaville',
                'Africa/Bujumbura' => 'Africa/Bujumbura',
                'Africa/Cairo' => 'Africa/Cairo',
                'Africa/Casablanca' => 'Africa/Casablanca',
                'Africa/Ceuta' => 'Africa/Ceuta',
                'Africa/Conakry' => 'Africa/Conakry',
                'Africa/Dakar' => 'Africa/Dakar',
                'Africa/Dar_es_Salaam' => 'Africa/Dar_es_Salaam',
                'Africa/Djibouti' => 'Africa/Djibouti',
                'Africa/Douala' => 'Africa/Douala',
                'Africa/El_Aaiun' => 'Africa/El_Aaiun',
                'Africa/Freetown' => 'Africa/Freetown',
                'Africa/Gaborone' => 'Africa/Gaborone',
                'Africa/Harare' => 'Africa/Harare',
                'Africa/Johannesburg' => 'Africa/Johannesburg',
                'Africa/Juba' => 'Africa/Juba',
                'Africa/Kampala' => 'Africa/Kampala',
                'Africa/Khartoum' => 'Africa/Khartoum',
                'Africa/Kigali' => 'Africa/Kigali',
                'Africa/Kinshasa' => 'Africa/Kinshasa',
                'Africa/Lagos' => 'Africa/Lagos',
                'Africa/Libreville' => 'Africa/Libreville',
                'Africa/Lome' => 'Africa/Lome',
                'Africa/Luanda' => 'Africa/Luanda',
                'Africa/Lubumbashi' => 'Africa/Lubumbashi',
                'Africa/Lusaka' => 'Africa/Lusaka',
                'Africa/Malabo' => 'Africa/Malabo',
                'Africa/Maputo' => 'Africa/Maputo',
                'Africa/Maseru' => 'Africa/Maseru',
                'Africa/Mbabane' => 'Africa/Mbabane',
                'Africa/Mogadishu' => 'Africa/Mogadishu',
                'Africa/Monrovia' => 'Africa/Monrovia',
                'Africa/Nairobi' => 'Africa/Nairobi',
                'Africa/Ndjamena' => 'Africa/Ndjamena',
                'Africa/Niamey' => 'Africa/Niamey',
                'Africa/Nouakchott' => 'Africa/Nouakchott',
                'Africa/Ouagadougou' => 'Africa/Ouagadougou',
                'Africa/Porto-Novo' => 'Africa/Porto-Novo',
                'Africa/Sao_Tome' => 'Africa/Sao_Tome',
                'Africa/Timbuktu' => 'Africa/Timbuktu',
                'Africa/Tripoli' => 'Africa/Tripoli',
                'Africa/Tunis' => 'Africa/Tunis',
                'Africa/Windhoek' => 'Africa/Windhoek',
            ],
            Yii::t('podium/view', 'America') => [
                'America/Adak' => 'America/Adak',
                'America/Anchorage' => 'America/Anchorage',
                'America/Anguilla' => 'America/Anguilla',
                'America/Antigua' => 'America/Antigua',
                'America/Araguaina' => 'America/Araguaina',
                'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
                'America/Argentina/Catamarca' => 'America/Argentina/Catamarca',
                'America/Argentina/ComodRivadavia' => 'America/Argentina/ComodRivadavia',
                'America/Argentina/Cordoba' => 'America/Argentina/Cordoba',
                'America/Argentina/Jujuy' => 'America/Argentina/Jujuy',
                'America/Argentina/La_Rioja' => 'America/Argentina/La_Rioja',
                'America/Argentina/Mendoza' => 'America/Argentina/Mendoza',
                'America/Argentina/Rio_Gallegos' => 'America/Argentina/Rio_Gallegos',
                'America/Argentina/Salta' => 'America/Argentina/Salta',
                'America/Argentina/San_Juan' => 'America/Argentina/San_Juan',
                'America/Argentina/San_Luis' => 'America/Argentina/San_Luis',
                'America/Argentina/Tucuman' => 'America/Argentina/Tucuman',
                'America/Argentina/Ushuaia' => 'America/Argentina/Ushuaia',
                'America/Aruba' => 'America/Aruba',
                'America/Asuncion' => 'America/Asuncion',
                'America/Atikokan' => 'America/Atikokan',
                'America/Atka' => 'America/Atka',
                'America/Bahia' => 'America/Bahia',
                'America/Bahia_Banderas' => 'America/Bahia_Banderas',
                'America/Barbados' => 'America/Barbados',
                'America/Belem' => 'America/Belem',
                'America/Belize' => 'America/Belize',
                'America/Blanc-Sablon' => 'America/Blanc-Sablon',
                'America/Boa_Vista' => 'America/Boa_Vista',
                'America/Bogota' => 'America/Bogota',
                'America/Boise' => 'America/Boise',
                'America/Buenos_Aires' => 'America/Buenos_Aires',
                'America/Cambridge_Bay' => 'America/Cambridge_Bay',
                'America/Campo_Grande' => 'America/Campo_Grande',
                'America/Cancun' => 'America/Cancun',
                'America/Caracas' => 'America/Caracas',
                'America/Catamarca' => 'America/Catamarca',
                'America/Cayenne' => 'America/Cayenne',
                'America/Cayman' => 'America/Cayman',
                'America/Chicago' => 'America/Chicago',
                'America/Chihuahua' => 'America/Chihuahua',
                'America/Coral_Harbour' => 'America/Coral_Harbour',
                'America/Cordoba' => 'America/Cordoba',
                'America/Costa_Rica' => 'America/Costa_Rica',
                'America/Creston' => 'America/Creston',
                'America/Cuiaba' => 'America/Cuiaba',
                'America/Curacao' => 'America/Curacao',
                'America/Danmarkshavn' => 'America/Danmarkshavn',
                'America/Dawson' => 'America/Dawson',
                'America/Dawson_Creek' => 'America/Dawson_Creek',
                'America/Denver' => 'America/Denver',
                'America/Detroit' => 'America/Detroit',
                'America/Dominica' => 'America/Dominica',
                'America/Edmonton' => 'America/Edmonton',
                'America/Eirunepe' => 'America/Eirunepe',
                'America/El_Salvador' => 'America/El_Salvador',
                'America/Ensenada' => 'America/Ensenada',
                'America/Fort_Wayne' => 'America/Fort_Wayne',
                'America/Fortaleza' => 'America/Fortaleza',
                'America/Glace_Bay' => 'America/Glace_Bay',
                'America/Godthab' => 'America/Godthab',
                'America/Goose_Bay' => 'America/Goose_Bay',
                'America/Grand_Turk' => 'America/Grand_Turk',
                'America/Grenada' => 'America/Grenada',
                'America/Guadeloupe' => 'America/Guadeloupe',
                'America/Guatemala' => 'America/Guatemala',
                'America/Guayaquil' => 'America/Guayaquil',
                'America/Guyana' => 'America/Guyana',
                'America/Halifax' => 'America/Halifax',
                'America/Havana' => 'America/Havana',
                'America/Hermosillo' => 'America/Hermosillo',
                'America/Indiana/Indianapolis' => 'America/Indiana/Indianapolis',
                'America/Indiana/Knox' => 'America/Indiana/Knox',
                'America/Indiana/Marengo' => 'America/Indiana/Marengo',
                'America/Indiana/Petersburg' => 'America/Indiana/Petersburg',
                'America/Indiana/Tell_City' => 'America/Indiana/Tell_City',
                'America/Indiana/Vevay' => 'America/Indiana/Vevay',
                'America/Indiana/Vincennes' => 'America/Indiana/Vincennes',
                'America/Indiana/Winamac' => 'America/Indiana/Winamac',
                'America/Indianapolis' => 'America/Indianapolis',
                'America/Inuvik' => 'America/Inuvik',
                'America/Iqaluit' => 'America/Iqaluit',
                'America/Jamaica' => 'America/Jamaica',
                'America/Jujuy' => 'America/Jujuy',
                'America/Juneau' => 'America/Juneau',
                'America/Kentucky/Louisville' => 'America/Kentucky/Louisville',
                'America/Kentucky/Monticello' => 'America/Kentucky/Monticello',
                'America/Knox_IN' => 'America/Knox_IN',
                'America/Kralendijk' => 'America/Kralendijk',
                'America/La_Paz' => 'America/La_Paz',
                'America/Lima' => 'America/Lima',
                'America/Los_Angeles' => 'America/Los_Angeles',
                'America/Louisville' => 'America/Louisville',
                'America/Lower_Princes' => 'America/Lower_Princes',
                'America/Maceio' => 'America/Maceio',
                'America/Managua' => 'America/Managua',
                'America/Manaus' => 'America/Manaus',
                'America/Marigot' => 'America/Marigot',
                'America/Martinique' => 'America/Martinique',
                'America/Matamoros' => 'America/Matamoros',
                'America/Mazatlan' => 'America/Mazatlan',
                'America/Mendoza' => 'America/Mendoza',
                'America/Menominee' => 'America/Menominee',
                'America/Merida' => 'America/Merida',
                'America/Metlakatla' => 'America/Metlakatla',
                'America/Mexico_City' => 'America/Mexico_City',
                'America/Miquelon' => 'America/Miquelon',
                'America/Moncton' => 'America/Moncton',
                'America/Monterrey' => 'America/Monterrey',
                'America/Montevideo' => 'America/Montevideo',
                'America/Montreal' => 'America/Montreal',
                'America/Montserrat' => 'America/Montserrat',
                'America/Nassau' => 'America/Nassau',
                'America/New_York' => 'America/New_York',
                'America/Nipigon' => 'America/Nipigon',
                'America/Nome' => 'America/Nome',
                'America/Noronha' => 'America/Noronha',
                'America/North_Dakota/Beulah' => 'America/North_Dakota/Beulah',
                'America/North_Dakota/Center' => 'America/North_Dakota/Center',
                'America/North_Dakota/New_Salem' => 'America/North_Dakota/New_Salem',
                'America/Ojinaga' => 'America/Ojinaga',
                'America/Panama' => 'America/Panama',
                'America/Pangnirtung' => 'America/Pangnirtung',
                'America/Paramaribo' => 'America/Paramaribo',
                'America/Phoenix' => 'America/Phoenix',
                'America/Port-au-Prince' => 'America/Port-au-Prince',
                'America/Port_of_Spain' => 'America/Port_of_Spain',
                'America/Porto_Acre' => 'America/Porto_Acre',
                'America/Porto_Velho' => 'America/Porto_Velho',
                'America/Puerto_Rico' => 'America/Puerto_Rico',
                'America/Rainy_River' => 'America/Rainy_River',
                'America/Rankin_Inlet' => 'America/Rankin_Inlet',
                'America/Recife' => 'America/Recife',
                'America/Regina' => 'America/Regina',
                'America/Resolute' => 'America/Resolute',
                'America/Rio_Branco' => 'America/Rio_Branco',
                'America/Rosario' => 'America/Rosario',
                'America/Santa_Isabel' => 'America/Santa_Isabel',
                'America/Santarem' => 'America/Santarem',
                'America/Santiago' => 'America/Santiago',
                'America/Santo_Domingo' => 'America/Santo_Domingo',
                'America/Sao_Paulo' => 'America/Sao_Paulo',
                'America/Scoresbysund' => 'America/Scoresbysund',
                'America/Shiprock' => 'America/Shiprock',
                'America/Sitka' => 'America/Sitka',
                'America/St_Barthelemy' => 'America/St_Barthelemy',
                'America/St_Johns' => 'America/St_Johns',
                'America/St_Kitts' => 'America/St_Kitts',
                'America/St_Lucia' => 'America/St_Lucia',
                'America/St_Thomas' => 'America/St_Thomas',
                'America/St_Vincent' => 'America/St_Vincent',
                'America/Swift_Current' => 'America/Swift_Current',
                'America/Tegucigalpa' => 'America/Tegucigalpa',
                'America/Thule' => 'America/Thule',
                'America/Thunder_Bay' => 'America/Thunder_Bay',
                'America/Tijuana' => 'America/Tijuana',
                'America/Toronto' => 'America/Toronto',
                'America/Tortola' => 'America/Tortola',
                'America/Vancouver' => 'America/Vancouver',
                'America/Virgin' => 'America/Virgin',
                'America/Whitehorse' => 'America/Whitehorse',
                'America/Winnipeg' => 'America/Winnipeg',
                'America/Yakutat' => 'America/Yakutat',
                'America/Yellowknife' => 'America/Yellowknife',
            ],
            Yii::t('podium/view', 'Antarctica') => [
                'Antarctica/Casey' => 'Antarctica/Casey',
                'Antarctica/Davis' => 'Antarctica/Davis',
                'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville',
                'Antarctica/Macquarie' => 'Antarctica/Macquarie',
                'Antarctica/Mawson' => 'Antarctica/Mawson',
                'Antarctica/McMurdo' => 'Antarctica/McMurdo',
                'Antarctica/Palmer' => 'Antarctica/Palmer',
                'Antarctica/Rothera' => 'Antarctica/Rothera',
                'Antarctica/South_Pole' => 'Antarctica/South_Pole',
                'Antarctica/Syowa' => 'Antarctica/Syowa',
                'Antarctica/Troll' => 'Antarctica/Troll',
                'Antarctica/Vostok' => 'Antarctica/Vostok',
                'Arctic/Longyearbyen' => 'Arctic/Longyearbyen',
            ],
            Yii::t('podium/view', 'Asia') => [
                'Asia/Aden' => 'Asia/Aden',
                'Asia/Almaty' => 'Asia/Almaty',
                'Asia/Amman' => 'Asia/Amman',
                'Asia/Anadyr' => 'Asia/Anadyr',
                'Asia/Aqtau' => 'Asia/Aqtau',
                'Asia/Aqtobe' => 'Asia/Aqtobe',
                'Asia/Ashgabat' => 'Asia/Ashgabat',
                'Asia/Ashkhabad' => 'Asia/Ashkhabad',
                'Asia/Baghdad' => 'Asia/Baghdad',
                'Asia/Bahrain' => 'Asia/Bahrain',
                'Asia/Baku' => 'Asia/Baku',
                'Asia/Bangkok' => 'Asia/Bangkok',
                'Asia/Beirut' => 'Asia/Beirut',
                'Asia/Bishkek' => 'Asia/Bishkek',
                'Asia/Brunei' => 'Asia/Brunei',
                'Asia/Calcutta' => 'Asia/Calcutta',
                'Asia/Chita' => 'Asia/Chita',
                'Asia/Choibalsan' => 'Asia/Choibalsan',
                'Asia/Chongqing' => 'Asia/Chongqing',
                'Asia/Chungking' => 'Asia/Chungking',
                'Asia/Colombo' => 'Asia/Colombo',
                'Asia/Dacca' => 'Asia/Dacca',
                'Asia/Damascus' => 'Asia/Damascus',
                'Asia/Dhaka' => 'Asia/Dhaka',
                'Asia/Dili' => 'Asia/Dili',
                'Asia/Dubai' => 'Asia/Dubai',
                'Asia/Dushanbe' => 'Asia/Dushanbe',
                'Asia/Gaza' => 'Asia/Gaza',
                'Asia/Harbin' => 'Asia/Harbin',
                'Asia/Hebron' => 'Asia/Hebron',
                'Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh',
                'Asia/Hong_Kong' => 'Asia/Hong_Kong',
                'Asia/Hovd' => 'Asia/Hovd',
                'Asia/Irkutsk' => 'Asia/Irkutsk',
                'Asia/Istanbul' => 'Asia/Istanbul',
                'Asia/Jakarta' => 'Asia/Jakarta',
                'Asia/Jayapura' => 'Asia/Jayapura',
                'Asia/Jerusalem' => 'Asia/Jerusalem',
                'Asia/Kabul' => 'Asia/Kabul',
                'Asia/Kamchatka' => 'Asia/Kamchatka',
                'Asia/Karachi' => 'Asia/Karachi',
                'Asia/Kashgar' => 'Asia/Kashgar',
                'Asia/Kathmandu' => 'Asia/Kathmandu',
                'Asia/Katmandu' => 'Asia/Katmandu',
                'Asia/Khandyga' => 'Asia/Khandyga',
                'Asia/Kolkata' => 'Asia/Kolkata',
                'Asia/Krasnoyarsk' => 'Asia/Krasnoyarsk',
                'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur',
                'Asia/Kuching' => 'Asia/Kuching',
                'Asia/Kuwait' => 'Asia/Kuwait',
                'Asia/Macao' => 'Asia/Macao',
                'Asia/Macau' => 'Asia/Macau',
                'Asia/Magadan' => 'Asia/Magadan',
                'Asia/Makassar' => 'Asia/Makassar',
                'Asia/Manila' => 'Asia/Manila',
                'Asia/Muscat' => 'Asia/Muscat',
                'Asia/Nicosia' => 'Asia/Nicosia',
                'Asia/Novokuznetsk' => 'Asia/Novokuznetsk',
                'Asia/Novosibirsk' => 'Asia/Novosibirsk',
                'Asia/Omsk' => 'Asia/Omsk',
                'Asia/Oral' => 'Asia/Oral',
                'Asia/Phnom_Penh' => 'Asia/Phnom_Penh',
                'Asia/Pontianak' => 'Asia/Pontianak',
                'Asia/Pyongyang' => 'Asia/Pyongyang',
                'Asia/Qatar' => 'Asia/Qatar',
                'Asia/Qyzylorda' => 'Asia/Qyzylorda',
                'Asia/Rangoon' => 'Asia/Rangoon',
                'Asia/Riyadh' => 'Asia/Riyadh',
                'Asia/Saigon' => 'Asia/Saigon',
                'Asia/Sakhalin' => 'Asia/Sakhalin',
                'Asia/Samarkand' => 'Asia/Samarkand',
                'Asia/Seoul' => 'Asia/Seoul',
                'Asia/Shanghai' => 'Asia/Shanghai',
                'Asia/Singapore' => 'Asia/Singapore',
                'Asia/Srednekolymsk' => 'Asia/Srednekolymsk',
                'Asia/Taipei' => 'Asia/Taipei',
                'Asia/Tashkent' => 'Asia/Tashkent',
                'Asia/Tbilisi' => 'Asia/Tbilisi',
                'Asia/Tehran' => 'Asia/Tehran',
                'Asia/Tel_Aviv' => 'Asia/Tel_Aviv',
                'Asia/Thimbu' => 'Asia/Thimbu',
                'Asia/Thimphu' => 'Asia/Thimphu',
                'Asia/Tokyo' => 'Asia/Tokyo',
                'Asia/Ujung_Pandang' => 'Asia/Ujung_Pandang',
                'Asia/Ulaanbaatar' => 'Asia/Ulaanbaatar',
                'Asia/Ulan_Bator' => 'Asia/Ulan_Bator',
                'Asia/Urumqi' => 'Asia/Urumqi',
                'Asia/Ust-Nera' => 'Asia/Ust-Nera',
                'Asia/Vientiane' => 'Asia/Vientiane',
                'Asia/Vladivostok' => 'Asia/Vladivostok',
                'Asia/Yakutsk' => 'Asia/Yakutsk',
                'Asia/Yekaterinburg' => 'Asia/Yekaterinburg',
                'Asia/Yerevan' => 'Asia/Yerevan',
            ],
            Yii::t('podium/view', 'Atlantic') => [
                'Atlantic/Azores' => 'Atlantic/Azores',
                'Atlantic/Bermuda' => 'Atlantic/Bermuda',
                'Atlantic/Canary' => 'Atlantic/Canary',
                'Atlantic/Cape_Verde' => 'Atlantic/Cape_Verde',
                'Atlantic/Faeroe' => 'Atlantic/Faeroe',
                'Atlantic/Faroe' => 'Atlantic/Faroe',
                'Atlantic/Jan_Mayen' => 'Atlantic/Jan_Mayen',
                'Atlantic/Madeira' => 'Atlantic/Madeira',
                'Atlantic/Reykjavik' => 'Atlantic/Reykjavik',
                'Atlantic/South_Georgia' => 'Atlantic/South_Georgia',
                'Atlantic/St_Helena' => 'Atlantic/St_Helena',
                'Atlantic/Stanley' => 'Atlantic/Stanley',
            ],
            Yii::t('podium/view', 'Australia') => [
                'Australia/ACT' => 'Australia/ACT',
                'Australia/Adelaide' => 'Australia/Adelaide',
                'Australia/Brisbane' => 'Australia/Brisbane',
                'Australia/Broken_Hill' => 'Australia/Broken_Hill',
                'Australia/Canberra' => 'Australia/Canberra',
                'Australia/Currie' => 'Australia/Currie',
                'Australia/Darwin' => 'Australia/Darwin',
                'Australia/Eucla' => 'Australia/Eucla',
                'Australia/Hobart' => 'Australia/Hobart',
                'Australia/LHI' => 'Australia/LHI',
                'Australia/Lindeman' => 'Australia/Lindeman',
                'Australia/Lord_Howe' => 'Australia/Lord_Howe',
                'Australia/Melbourne' => 'Australia/Melbourne',
                'Australia/North' => 'Australia/North',
                'Australia/NSW' => 'Australia/NSW',
                'Australia/Perth' => 'Australia/Perth',
                'Australia/Queensland' => 'Australia/Queensland',
                'Australia/South' => 'Australia/South',
                'Australia/Sydney' => 'Australia/Sydney',
                'Australia/Tasmania' => 'Australia/Tasmania',
                'Australia/Victoria' => 'Australia/Victoria',
                'Australia/West' => 'Australia/West',
                'Australia/Yancowinna' => 'Australia/Yancowinna',
            ],
            Yii::t('podium/view', 'Europe') => [
                'Europe/Amsterdam' => 'Europe/Amsterdam',
                'Europe/Andorra' => 'Europe/Andorra',
                'Europe/Athens' => 'Europe/Athens',
                'Europe/Belfast' => 'Europe/Belfast',
                'Europe/Belgrade' => 'Europe/Belgrade',
                'Europe/Berlin' => 'Europe/Berlin',
                'Europe/Bratislava' => 'Europe/Bratislava',
                'Europe/Brussels' => 'Europe/Brussels',
                'Europe/Bucharest' => 'Europe/Bucharest',
                'Europe/Budapest' => 'Europe/Budapest',
                'Europe/Busingen' => 'Europe/Busingen',
                'Europe/Chisinau' => 'Europe/Chisinau',
                'Europe/Copenhagen' => 'Europe/Copenhagen',
                'Europe/Dublin' => 'Europe/Dublin',
                'Europe/Gibraltar' => 'Europe/Gibraltar',
                'Europe/Guernsey' => 'Europe/Guernsey',
                'Europe/Helsinki' => 'Europe/Helsinki',
                'Europe/Isle_of_Man' => 'Europe/Isle_of_Man',
                'Europe/Istanbul' => 'Europe/Istanbul',
                'Europe/Jersey' => 'Europe/Jersey',
                'Europe/Kaliningrad' => 'Europe/Kaliningrad',
                'Europe/Kiev' => 'Europe/Kiev',
                'Europe/Lisbon' => 'Europe/Lisbon',
                'Europe/Ljubljana' => 'Europe/Ljubljana',
                'Europe/London' => 'Europe/London',
                'Europe/Luxembourg' => 'Europe/Luxembourg',
                'Europe/Madrid' => 'Europe/Madrid',
                'Europe/Malta' => 'Europe/Malta',
                'Europe/Mariehamn' => 'Europe/Mariehamn',
                'Europe/Minsk' => 'Europe/Minsk',
                'Europe/Monaco' => 'Europe/Monaco',
                'Europe/Moscow' => 'Europe/Moscow',
                'Europe/Nicosia' => 'Europe/Nicosia',
                'Europe/Oslo' => 'Europe/Oslo',
                'Europe/Paris' => 'Europe/Paris',
                'Europe/Podgorica' => 'Europe/Podgorica',
                'Europe/Prague' => 'Europe/Prague',
                'Europe/Riga' => 'Europe/Riga',
                'Europe/Rome' => 'Europe/Rome',
                'Europe/Samara' => 'Europe/Samara',
                'Europe/San_Marino' => 'Europe/San_Marino',
                'Europe/Sarajevo' => 'Europe/Sarajevo',
                'Europe/Simferopol' => 'Europe/Simferopol',
                'Europe/Skopje' => 'Europe/Skopje',
                'Europe/Sofia' => 'Europe/Sofia',
                'Europe/Stockholm' => 'Europe/Stockholm',
                'Europe/Tallinn' => 'Europe/Tallinn',
                'Europe/Tirane' => 'Europe/Tirane',
                'Europe/Tiraspol' => 'Europe/Tiraspol',
                'Europe/Uzhgorod' => 'Europe/Uzhgorod',
                'Europe/Vaduz' => 'Europe/Vaduz',
                'Europe/Vatican' => 'Europe/Vatican',
                'Europe/Vienna' => 'Europe/Vienna',
                'Europe/Vilnius' => 'Europe/Vilnius',
                'Europe/Volgograd' => 'Europe/Volgograd',
                'Europe/Warsaw' => 'Europe/Warsaw',
                'Europe/Zagreb' => 'Europe/Zagreb',
                'Europe/Zaporozhye' => 'Europe/Zaporozhye',
                'Europe/Zurich' => 'Europe/Zurich',
            ],
            Yii::t('podium/view', 'Indian') => [
                'Indian/Antananarivo' => 'Indian/Antananarivo',
                'Indian/Chagos' => 'Indian/Chagos',
                'Indian/Christmas' => 'Indian/Christmas',
                'Indian/Cocos' => 'Indian/Cocos',
                'Indian/Comoro' => 'Indian/Comoro',
                'Indian/Kerguelen' => 'Indian/Kerguelen',
                'Indian/Mahe' => 'Indian/Mahe',
                'Indian/Maldives' => 'Indian/Maldives',
                'Indian/Mauritius' => 'Indian/Mauritius',
                'Indian/Mayotte' => 'Indian/Mayotte',
                'Indian/Reunion' => 'Indian/Reunion',
            ],
            Yii::t('podium/view', 'Pacific') => [
                'Pacific/Apia' => 'Pacific/Apia',
                'Pacific/Auckland' => 'Pacific/Auckland',
                'Pacific/Bougainville' => 'Pacific/Bougainville',
                'Pacific/Chatham' => 'Pacific/Chatham',
                'Pacific/Chuuk' => 'Pacific/Chuuk',
                'Pacific/Easter' => 'Pacific/Easter',
                'Pacific/Efate' => 'Pacific/Efate',
                'Pacific/Enderbury' => 'Pacific/Enderbury',
                'Pacific/Fakaofo' => 'Pacific/Fakaofo',
                'Pacific/Fiji' => 'Pacific/Fiji',
                'Pacific/Funafuti' => 'Pacific/Funafuti',
                'Pacific/Galapagos' => 'Pacific/Galapagos',
                'Pacific/Gambier' => 'Pacific/Gambier',
                'Pacific/Guadalcanal' => 'Pacific/Guadalcanal',
                'Pacific/Guam' => 'Pacific/Guam',
                'Pacific/Honolulu' => 'Pacific/Honolulu',
                'Pacific/Johnston' => 'Pacific/Johnston',
                'Pacific/Kiritimati' => 'Pacific/Kiritimati',
                'Pacific/Kosrae' => 'Pacific/Kosrae',
                'Pacific/Kwajalein' => 'Pacific/Kwajalein',
                'Pacific/Majuro' => 'Pacific/Majuro',
                'Pacific/Marquesas' => 'Pacific/Marquesas',
                'Pacific/Midway' => 'Pacific/Midway',
                'Pacific/Nauru' => 'Pacific/Nauru',
                'Pacific/Niue' => 'Pacific/Niue',
                'Pacific/Norfolk' => 'Pacific/Norfolk',
                'Pacific/Noumea' => 'Pacific/Noumea',
                'Pacific/Pago_Pago' => 'Pacific/Pago_Pago',
                'Pacific/Palau' => 'Pacific/Palau',
                'Pacific/Pitcairn' => 'Pacific/Pitcairn',
                'Pacific/Pohnpei' => 'Pacific/Pohnpei',
                'Pacific/Ponape' => 'Pacific/Ponape',
                'Pacific/Port_Moresby' => 'Pacific/Port_Moresby',
                'Pacific/Rarotonga' => 'Pacific/Rarotonga',
                'Pacific/Saipan' => 'Pacific/Saipan',
                'Pacific/Samoa' => 'Pacific/Samoa',
                'Pacific/Tahiti' => 'Pacific/Tahiti',
                'Pacific/Tarawa' => 'Pacific/Tarawa',
                'Pacific/Tongatapu' => 'Pacific/Tongatapu',
                'Pacific/Truk' => 'Pacific/Truk',
                'Pacific/Wake' => 'Pacific/Wake',
                'Pacific/Wallis' => 'Pacific/Wallis',
                'Pacific/Yap' => 'Pacific/Yap',
            ]
        ];
    }
}