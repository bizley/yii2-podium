<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use bizley\podium\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/**
 * Podium Helper
 * Static methods for html output and other little things.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class Helper
{
    
    /**
     * Gets image source for default avatar image in base64.
     * @return string image source
     */
    public static function defaultAvatar()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPoAAAD6CAMAAAC/MqoPAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjRGNTcxMDVGRTQ3MDExRTQ4MUUyREZENUQ4MzQwNURGIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjRGNTcxMDYwRTQ3MDExRTQ4MUUyREZENUQ4MzQwNURGIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NEY1NzEwNURFNDcwMTFFNDgxRTJERkQ1RDgzNDA1REYiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NEY1NzEwNUVFNDcwMTFFNDgxRTJERkQ1RDgzNDA1REYiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5Mv8iSAAAAJ1BMVEXz8/P////9/f309PT5+fn29vb7+/v19fX4+Pj39/f8/Pz6+vr+/v5CXjXPAAAEKUlEQVR42uydaa6bIQxFY+Zp/+vt8KNVpSf1JQFzneuzgyOM7Y+A83g4juM4juM4juM4juM4juM4juM4juM4juM4juM4juM4juM4z1NzS2WEtURkrTBKarl+vHXMrQT5ilBajp/r3efX2n/0Z4+M3p9qX9uQ7zLaB238moI8Q0iVU/xj5GN7Xvy3fLO+53uRVynd9JKnJa+zkt2Fz0Xeo2Sj5i/u8n93vM1glx0YDPo6ZQ/TWpmrRXZRbLnnIfsYmdXclHvda/7TvfLtc2P7PU7ZzzRR45KcIFno4eQM+H1dDofUA3qaj0VOUSLjRrew3fs6qL46Z7ijh3yTs+Bm+RoOq4fKmOOwM93xRcdd9vOLjrrsCouOuuxNNEBM8nGoqA/A2t5FB8CWbiqpT7x4D0rqIbLGO2DETzX1yRrveBGfRQ+wk6qmqA7W1RRF9YKlHhTVA9ani2hSWbMcWJ5rqupQeS6pqifWBA+W4oeq+mCtbWDVbamqL1eHQHRxdVf3ve7qXte9m/Me3r/c/HvdT2n8bM5PZP0c3n998d/cvgPxL63Ev68z36ogvktDfIOK+d4c8W1J4juyzDejie/DM7+CIH77wvziifidG/PrRuI3rcwvmZnfrxNPLWCeVcE8oYR5Lg3xNCLmGVTMk8eY580xTxlkni3JPFH0QTxH9sE8PZh5ZvTjrUnhw/Sk8F9z8V9ed9vj8XN6K9OFlPlW/O92ryZT3JbzmmAv17VtXfywVdvz1k92Q+V9U6wbjPp84EDaxsK3I8fRBrr5euwmFfrXaz94mQi7s21Hf2EHDvp4/FoFaqavChcmMTd8VnkJgVjlstILR7yj6a72FiB0VnM09676lBnJXdccyV3bHMc9q5uj3KzJQy6AUOPqFXOE6waxyCWu36Obco3Lb5+SXOTqhbK2bqqvxlXWMEpcLXKZa/dsplznUqprAkBj3Oj3tnssAsGFziYJCOrVvS8Ude3XQCjhfiHkkwChGvJ5IakvzSxfBIpC1sxcaWxuHcwAHNkkgSMx5jjVTDcFEJVPuC6QdL7CpljgmoByvMDFgap+fEQT7KIfX3bcRT++7MCLfnrZB7L6IKzpGrW9YKsfrO1ZwDnXyU909WOdfF3o6uvUd3sSeA59t8eAr35owG4XA3TGynawvuVlQf3ISVUSEyS29v1oI9/FCJ2vkzvW0Vko6odKu5l43x/x0476ZI337RFvKN53R/y0pL434oMl9a1//ZXFFJmvfz/Rxxdb6hu/XOuypb7xiK6LMTpnadtb3oY19W0f7VXMUVm3+r7NPu2pT9atvm2zx2VPfUXGBn5nG98sqjfWLLcrzw2L6oM1y23Kcyaz3J4812yqN9YstyfPFZvqO05qgk31DceyUYwSWRP8jhTfraq//8merKon1tr2/+r2Q4ABAIdWbQqxqb1TAAAAAElFTkSuQmCC';
    }
    
    /**
     * Gets user tag for deleted user.
     * @param boolean $simple wheter to return simple tag instead of full
     * @return string tag
     */
    public static function deletedUserTag($simple = false)
    {
        return self::podiumUserTag('', 0, null, null, $simple);
    }
    
    /**
     * Gets HTMLPurifier configuration set.
     * @param string $type set name
     * @return array configuration
     */
    public static function podiumPurifierConfig($type = '')
    {
        $config = [];
        
        switch ($type) {
            case 'full':
                $config = [
                    'HTML.Allowed' => 'img[src|style|class|alt],a[href|target],br,p,span[style|class],hr,ul,ol,li,blockquote,pre,sup,sub,h1,h2,h3,h4,h5,h6,table[class],tbody,tr,td[style],small,b,strong,i,em,u',
                    'Attr.AllowedFrameTargets' => ['_blank']
                ];
                break;
            default:
                $config = [
                    'HTML.Allowed' => 'img[src|style|class|alt],a[href|target],br,p,span[style],hr,ul,ol,li,b,strong,i,em,u',
                    'Attr.AllowedFrameTargets' => ['_blank']
                ];
        }
        
        
        return $config;
    }
    
    /**
     * Gets user tag.
     * @param string $name user name
     * @param integer $role user role
     * @param integer|null $id user ID
     * @param boolean $simple wheter to return simple tag instead of full
     * @return string tag
     */
    public static function podiumUserTag($name, $role, $id = null, $slug = null, $simple = false)
    {
        $icon = Html::tag('span', '', ['class' => $id ? 'glyphicon glyphicon-user' : 'glyphicon glyphicon-ban-circle']);
        $url = $id ? ['members/view', 'id' => $id, 'slug' => $slug] : '#';
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
    
    /**
     * Gets quote html.
     * @param \bizley\podium\models\Post $post post model to be quoted
     * @param string $quote partial text to be quoted
     * @return string quote html
     */
    public static function prepareQuote($post, $quote = '')
    {
        $content = !empty($quote) ? nl2br(HtmlPurifier::process($quote)) : $post->content;
        return Html::tag('blockquote', Html::tag('small', $post->user->getPodiumTag() . ' @ ' . Yii::$app->formatter->asDatetime($post->created_at)) . $content) . '<br>';
    }
    
    /**
     * Gets background image style base64 encoded.
     * @return string style
     */
    public static function replyBgd()
    {
        return 'style="background-repeat:repeat-y;background-position:top right;background-image:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAUCAYAAAB7wJiVAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkI2NTVERkRDREEyRTExRTRCRkI5OEQyMTc5QURDMkNDIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkI2NTVERkREREEyRTExRTRCRkI5OEQyMTc5QURDMkNDIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6QjY1NURGREFEQTJFMTFFNEJGQjk4RDIxNzlBREMyQ0MiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6QjY1NURGREJEQTJFMTFFNEJGQjk4RDIxNzlBREMyQ0MiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6k5fCuAAAAyklEQVR42uyXsQ2DMBBFIcooLnCVBajcpaXOJqnZhDojULEDltxkE3KWLpKVAtxwSvGe9EXBr/4TSNemlBqllwySIPGSVTJLXpIlF5xzDZzLVZ8PyVPSFe9umrtklEzMdT4X/TJ+ZZR0+r5nLhshw46MUsrAXDZCQmU3MJeNEF/Z9cxlI2St7K7MZSNkruzOzGUjJN8Z8aAXtQcGQha9M+KOjPF7HILNYZiPvvfRpQ7n027bxgp/9ssChABCEAIIQQggBCFgyUeAAQBSzyA8dwka9AAAAABJRU5ErkJggg==\');"';
    }
    
    /**
     * Gets role label html.
     * @param integer|null $role role ID
     * @return string label html
     */
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
    
    /**
     * Gets sorting icon.
     * @param string|null $attribute sorting attribute name
     * @return string|null icon html or null if empty attribute
     */
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
    
    /**
     * Gets User status label.
     * @param integer|null $status status ID
     * @return string label html
     */
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
    
    /**
     * Gets SummerNote toolbars.
     * @param string $type name of the set
     * @return array toolbars configuration
     */
    public static function summerNoteToolbars($type = 'minimal')
    {
        $toolbars = [];
        
        switch ($type) {
            case 'full':
                $toolbars = [
                    ['css', ['style']],
                    ['clear', ['clear']],
                    ['style', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'hr', 'table']],
                    ['misc', ['codeview']],
                ];
                break;
            default:
                $toolbars = [
                    ['style', ['bold', 'italic', 'underline']],
                    ['para', ['ul', 'ol']],
                    ['insert', ['link', 'picture']],
                ];
        }
        
        return $toolbars;
    }

    /**
     * Gets timezones array.
     * http://php.net/manual/en/timezones.php
     * @return array timezones
     */
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