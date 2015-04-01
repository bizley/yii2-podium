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
}