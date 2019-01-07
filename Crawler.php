<?php
$file_path = realpath(__FILE__);
$script_path = str_replace(basename(__FILE__), "", $GLOBALS['file_path']);
//require_once($GLOBALS['script_path'] . "simple_html_dom.php");

class Crawler
{
    private $game_nm;
    private $base_url;


    function __construct()
    {
        header("Content-Type: application/json; charset=utf-8;");
//        $this->game_nm = 'tekkenvii';
        $this->game_nm = 'playerunknownsbattlegrounds';

        $this->base_url = 'https://isthereanydeal.com/game/' . $this->game_nm . '/info/';
        $lowest_row = [];
        // 크롤링할 파일 경로 획득
        $target_name = "tekken_7_html.txt";
        $script_path = $GLOBALS['script_path'];
        $target_path = "{$script_path}{$target_name}";

//        // 크롤링할 파일이 있는지 확인하고 없으면 다운로드
//        $this->CheckHTMLExists($target_path);

        // 무조건 크롤링할 파일 다운로드
        $this->GetHTML($this->base_url, $target_path);

        // 크롤링할 파일 불러오기
        $html_arr = $this->ReadFile($target_path, '|');

        // tekken 페이지에서 모든 플랫폼 이름, 할인율, 현재가, 최저가, 발행가를 얻음
        $platform_nms = $this->Parse($html_arr,'/' . $this->game_nm . '"]\'>(.*)<\/a>/U');
        $discount_rates = $this->Parse($html_arr, '/priceTable__cut t-st3__num\'>(.*)<\/td>/U');
        $currents= $this->Parse($html_arr, '/priceTable__new t-st3__price \'>(.*)<\/td>/U');
        $lowests= $this->Parse($html_arr, '/priceTable__low t-st3__price s-low g-low\'>(.*)<\/td>/U');
        $regulars= $this->Parse($html_arr, '/priceTable__old t-st3__price\'>(.*)<\/td>/U');

        // 가장 첫 요소를 뽑아 최저가 행만 추출
        $lowest_row["platform_nms"] = $platform_nms[0];
        $lowest_row["discount_rates"] = $discount_rates[0];
        $lowest_row["currents"] = $currents[0];
        $lowest_row["lowests"] = $lowests[0];
        $lowest_row["regulars"] = $regulars[0];

        // 초저가 행 출력
        $lowest_row = json_encode($lowest_row);
        print_r($lowest_row);
    }

    function CheckHTMLExists($html_path)
    {
        if (!file_exists($html_path)) {
            $this->GetHTML($this->base_url, $html_path);
        }
    }

    function GetHTML($url, $html_path)
    {
        system('curl ' . $url . ' > ' . $html_path);
    }

    function ReadFile($file_name, $delimiter = ',', $mode = 'r')
    {
        $fp = fopen($file_name, $mode) or die("파일을 생성할 수 없습니다.");
        $loaded_file = array();
        while ($row = fgetcsv($fp, 0, $delimiter)) {
            $loaded_file[] = $row;
        }
        return $loaded_file;
    }

    function Parse($html_arr, $pattern)
    {
        foreach($html_arr as $row) {
            ;
            $row = $row[0];

            $output = $this->GetMatched($pattern, $row);
            if ($output) {
                $matched = $output[1];
                return $matched;
            }
        }
    }

    function GetMatched($pattern, $string)
    {
        preg_match_all($pattern, $string, $output);

        if (count($output) < 2) {
            exit('정규식 체크 결과값이 비정상입니다. 결과 배열의 길이가 2 미만입니다.' . PHP_EOL);
        } elseif (!empty($output[0])) {
            return $output;
        } else {
            return false; // 빈 배열
        }
    }

}

$a = new Crawler();


