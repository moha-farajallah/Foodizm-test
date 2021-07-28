<?php
namespace Veriteworks\Gmo\Model\Source;

/**
 * Class Cvstypes
 * @package Veriteworks\Gmo\Model\Source
 */
class Cvstypes
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '00007', 'label' => __('SevenEleven')],
            ['value' => '10001', 'label' => __('Lawson')],
            ['value' => '10002', 'label' => __('FamilyMart')],
//            ['value' => '10003', 'label' => __('Sunkus')],
//            ['value' => '10004', 'label' => __('CircleK')],
            ['value' => '10005', 'label' => __('MiniStop')],
            ['value' => '00006', 'label' => __('DailyYamazaki')],
            ['value' => '10008', 'label' => __('Seico-Mart')],
//            ['value' => '00009', 'label' => __('Three F')],
//            ['value' => '10001', 'label' => __('Lawson')],
//            ['value' => '10002', 'label' => __('Sunkus')],
//            ['value' => '10005', 'label' => __('MiniStop')],
                ];
    }

    public function getCvsType($cvsType)
    {
        $code = "";
        switch ($cvsType) {
            case '00007':
                $code = __('SevenEleven');
                break;
            case '10001':
            case '00001':
                $code = __('Lawson');
                break;
            case '10002':
            case '00002':
                $code = __('FamilyMart');
                break;
            case '10003':
            case '00003':
                $code = __('Sunkus');
                break;
            case '10004':
            case '00004':
                $code = __('CircleK');
                break;
            case '10005':
            case '00005':
                $code = __('MiniStop');
                break;
            case '10006':
            case '00006':
                $code = __('DailyYamazaki');
                break;
            case '00008':
            case '10008':
                $code = __('Seico-Mart');
                break;
            case '00009':
                $code = __('Three F');
                break;
        }

        return $code;
    }
}
