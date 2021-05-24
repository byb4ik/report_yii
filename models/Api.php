<?php


namespace app\models;


class Api
{
    public function getValuteType(string $typeValute)
    {
        switch ($typeValute) {
            case 'RUB':
            case 'rub':
                return 'RUB';
            case 'USD':
            case 'usd':
                return 'USD';
            default:
                return 'NAN';
        }
    }

    public function getAccountGroups(string $methodName, $params): array
    {

        $url = 'https://webtrader.utip.org:8085/api/v2/' . $methodName;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $http_result = curl_exec($ch);
        $result = json_decode($http_result, true);

        return $result;
    }

    public static function getRealGroup($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ('1' !== $value['groupIsDemo']) {
                $result[] = $value;
            }
        }
        return $result;
    }

    public static function getOrdersByGroup($data, $id)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ($id === $value['groupID']) {
                $result[] = $value;
            }
        }
        return $result;
    }
}