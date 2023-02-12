<?php

use PCore\ClickHouse\JSONEachRowStreamResponseParser;
use PHPUnit\Framework\TestCase;

/**
 * Class JSONEachRowStreamResponseParerTest
 * @package PCore\ClickHouse
 * @github https://github.com/pcore-framework/clickhouse
 */
class JSONEachRowStreamResponseParerTest extends TestCase
{

    /**
     * @return void
     * @throws JsonException
     */
    public function testCanReadConsistentRowsFromResponse()
    {
        $rows = '
            {"date":"2023-01-16","user_id":"100991174365","device_id":"","name":"game_tournament_picture_view"}
            {"date":"2023-01-16","user_id":"571720067392","device_id":"","name":"91pct_user_info"}
            {"date":"2023-01-16","user_id":"513942215025","device_id":"","name":"game_tournament_picture_view"}
            {"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"100pct_enter_menu"}
            {"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"98pct_map"}
        ';

        $expectedResult = [];
        $preparedExpectedResult = explode("\n", $rows);
        foreach ($preparedExpectedResult as $item) {
            $expectedResult[] = json_decode($item, true);
        }

        $sut = new JSONEachRowStreamResponseParser();
        $sut->add($rows);

        $actualResult = iterator_to_array($sut->row());
        foreach ($expectedResult as $i => $row) {
            $this->assertArrayHasKey($i, $actualResult);
            $this->assertIsArray($row);
            $this->assertEquals($row, $actualResult[$i]);
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testCanReadInconsistentRowsFrom2DifferentBlocks()
    {
        $rowsBlock1 = '
            {"date":"2023-01-16","user_id":"100991174365","device_id":"","name":"game_tournament_picture_view"}
            {"date":"2023-01-16","user_id":"571720067392","device_id":"","name":"91pct_user_info"}
            {"date":"2023-01-16","user_id":"513942215025","device_id":"","name":"game_t
        ';

        $rowsBlock2 = '
            ournament_picture_view"}
            {"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"100pct_enter_menu"}
            {"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"98pct_map"}
        ';

        $expectedResult = [
            json_decode('{"date":"2023-01-16","user_id":"100991174365","device_id":"","name":"game_tournament_picture_view"}', true),
            json_decode('{"date":"2023-01-16","user_id":"571720067392","device_id":"","name":"91pct_user_info"}', true),
            json_decode('{"date":"2023-01-16","user_id":"513942215025","device_id":"","name":"game_tournament_picture_view"}', true),
            json_decode('{"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"100pct_enter_menu"}', true),
            json_decode('{"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"98pct_map"}', true),
        ];

        $sut = new JSONEachRowStreamResponseParser();
        $sut->add($rowsBlock1);
        $sut->add($rowsBlock2);

        $actualResult = iterator_to_array($sut->row());
        foreach ($expectedResult as $i => $row) {
            $this->assertArrayHasKey($i, $actualResult);
            $this->assertIsArray($row);
            $this->assertEquals($row, $actualResult[$i]);
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testCanReadInconsistentRowsFromMultipleDifferentBlocks()
    {
        $rowsBlock1 = '{"date":"2023-01-16","user_id":"100991174365","device_id":"","name":"game_tournament_picture_view"}
{"date":"2023-01-16","user_id":"571720';

        $rowsBlock2 = '067392","device_id":"","name":"91pct_user_info"}
{"date":"2023-01-16","user_id":"513942215025","device_id":"","name":"game_t';

        $rowsBlock3 = 'ournament_picture_view"}
{"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"100pct_enter_menu"}
{"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"98pct_map"}';

        $expectedResult = [
            json_decode('{"date":"2023-01-16","user_id":"100991174365","device_id":"","name":"game_tournament_picture_view"}', true),
            json_decode('{"date":"2023-01-16","user_id":"571720067392","device_id":"","name":"91pct_user_info"}', true),
            json_decode('{"date":"2023-01-16","user_id":"513942215025","device_id":"","name":"game_tournament_picture_view"}', true),
            json_decode('{"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"100pct_enter_menu"}', true),
            json_decode('{"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"98pct_map"}', true),
        ];

        $sut = new JSONEachRowStreamResponseParser();
        $sut->add($rowsBlock1);
        $sut->add($rowsBlock2);
        $sut->add($rowsBlock3);

        $actualResult = iterator_to_array($sut->row());
        foreach ($expectedResult as $i => $row) {
            $this->assertArrayHasKey($i, $actualResult);
            $this->assertIsArray($row);
            $this->assertEquals($row, $actualResult[$i]);
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testCanReadInconsistentRowsFromDifferentBlocksWithInnerJSON()
    {
        $rowsBlock1 = '{"date":"2023-01-16","user_id":"5346906","device_id":"61feb9f44871e32458777ec6","name":"91pct_user_info","user":"{\"referrer.ios.ad_referrer.adgroup_id_visit\":\"\",\"referrer.ios.ad_referrer.ad_id_install\":\"\",\"referrer.ios.ad_referrer.adset_id_install\":\"\",\"referrer.ios.ad_referrer.adset_id_visit\":\"\",\"referrer.ios.ad_referrer.agency_visit\":\"\",\"referrer.ios.ad_referrer.ad_id_visit\":\"\",\"referrer.ios.ad_referrer.agency_install\":\"\",\"referrer.ios.ad_referrer.adgroup_id_install\":\"\",\"referrer.ios.ad_referrer.media_source_visit\":\"\",\"referrer.ios.ad_referrer.media_source_install\":\"organic\",\"referrer.ios.ad_referrer.campaign_id_install\":\"\",\"referrer.ios.ad_referrer.campaign_id_visit\":\"\",\"referrer.ios.install_id\":\"\",\"referrer.ios.install\":\"direct\",\"referrer.ios.appsflyer_install\":\"organic\",\"referrer.ios.return_id\":\"\",\"referrer.ios.visit\":\"direct\",\"referrer.io';
        $rowsBlock2 = 's.visit_id\":\"\",\"referrer.ios.return\":\"\",\"referrer.ios.mmp_install\":\"\",\"login_count.ios\":217,\"game_data.level\":105,\"game_data.consumables.coins_scale\":1,\"game_data.twins_shown\":13,\"game_data.episode\":7,\"game_data.pictures_shown\":1303,\"split.ios.current.64462133fbcef6ab368fd1c85c8.group_id\":\"data3\",\"split.ios.current.64462133fbcef6ab368fd1c85c8.id\":\"64462133fbcef6ab368fd1c85c8\",\"return_time.ios\":0,\"login_time.ios\":1648760398,\"payments.ios.total.amount\":0,\"payments.ios.total.count\":0,\"register_time.ios\":1644083700,\"info.country\":\"US\",\"info.gender\":\"unknown\",\"info.age\":52}"}';
        $rowsBlock3 = '{"date":"2023-01-16","user_id":"513942215025","device_id":"","name":"game_tournament_round_finish","user":"{\"referrer.cm.ad_re';
        $rowsBlock4 = 'ferrer\":[],\"referrer.cm.install_id\":\"568541006131\",\"referrer.cm.install\":\"passive_friend_invitation\",\"referrer.cm.visit\":\"rb_9\",\"referrer.cm.visit_id\":\"\",\"login_count.cm\":7479,\"game_data.level\":1861,\"game_data.consumables.coins_scale\":1,\"game_data.twins_shown\":0,\"game_data.episode\":124,\"game_data.pictures_shown\":54001,\"split.cm.current.27062287bd7175b1f5d5308e478.group_id\":\"data1\",\"split.cm.current.27062287bd7175b1f5d5308e478.id\":\"27062287bd7175b1f5d5308e478\",\"return_time.cm\":0,\"login_time.cm\":1648756109,\"payments.cm.total.amount\":30,\"payments.cm.total.count\":1,\"register_time.cm\":1497207873,\"info.country\":\"RU\",\"info.gender\":\"female\",\"info.age\":73}"}';

        $expectedResult = [
            json_decode('{"date":"2023-01-16","user_id":"5346906","device_id":"61feb9f44871e32458777ec6","name":"91pct_user_info","user":"{\"referrer.ios.ad_referrer.adgroup_id_visit\":\"\",\"referrer.ios.ad_referrer.ad_id_install\":\"\",\"referrer.ios.ad_referrer.adset_id_install\":\"\",\"referrer.ios.ad_referrer.adset_id_visit\":\"\",\"referrer.ios.ad_referrer.agency_visit\":\"\",\"referrer.ios.ad_referrer.ad_id_visit\":\"\",\"referrer.ios.ad_referrer.agency_install\":\"\",\"referrer.ios.ad_referrer.adgroup_id_install\":\"\",\"referrer.ios.ad_referrer.media_source_visit\":\"\",\"referrer.ios.ad_referrer.media_source_install\":\"organic\",\"referrer.ios.ad_referrer.campaign_id_install\":\"\",\"referrer.ios.ad_referrer.campaign_id_visit\":\"\",\"referrer.ios.install_id\":\"\",\"referrer.ios.install\":\"direct\",\"referrer.ios.appsflyer_install\":\"organic\",\"referrer.ios.return_id\":\"\",\"referrer.ios.visit\":\"direct\",\"referrer.ios.visit_id\":\"\",\"referrer.ios.return\":\"\",\"referrer.ios.mmp_install\":\"\",\"login_count.ios\":217,\"game_data.level\":105,\"game_data.consumables.coins_scale\":1,\"game_data.twins_shown\":13,\"game_data.episode\":7,\"game_data.pictures_shown\":1303,\"split.ios.current.64462133fbcef6ab368fd1c85c8.group_id\":\"data3\",\"split.ios.current.64462133fbcef6ab368fd1c85c8.id\":\"64462133fbcef6ab368fd1c85c8\",\"return_time.ios\":0,\"login_time.ios\":1648760398,\"payments.ios.total.amount\":0,\"payments.ios.total.count\":0,\"register_time.ios\":1644083700,\"info.country\":\"US\",\"info.gender\":\"unknown\",\"info.age\":52}"}', true),
            json_decode('{"date":"2023-01-16","user_id":"513942215025","device_id":"","name":"game_tournament_round_finish","user":"{\"referrer.cm.ad_referrer\":[],\"referrer.cm.install_id\":\"568541006131\",\"referrer.cm.install\":\"passive_friend_invitation\",\"referrer.cm.visit\":\"rb_9\",\"referrer.cm.visit_id\":\"\",\"login_count.cm\":7479,\"game_data.level\":1861,\"game_data.consumables.coins_scale\":1,\"game_data.twins_shown\":0,\"game_data.episode\":124,\"game_data.pictures_shown\":54001,\"split.cm.current.27062287bd7175b1f5d5308e478.group_id\":\"data1\",\"split.cm.current.27062287bd7175b1f5d5308e478.id\":\"27062287bd7175b1f5d5308e478\",\"return_time.cm\":0,\"login_time.cm\":1648756109,\"payments.cm.total.amount\":30,\"payments.cm.total.count\":1,\"register_time.cm\":1497207873,\"info.country\":\"RU\",\"info.gender\":\"female\",\"info.age\":73}"}', true),
        ];

        $sut = new JSONEachRowStreamResponseParser();
        $sut->add($rowsBlock1);
        $sut->add($rowsBlock2);
        $sut->add($rowsBlock3);
        $sut->add($rowsBlock4);

        $actualResult = iterator_to_array($sut->row());
        foreach ($expectedResult as $i => $row) {
            $this->assertArrayHasKey($i, $actualResult);
            $this->assertIsArray($row);
            $this->assertEquals($row, $actualResult[$i]);
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testCanReadInconsistentLongRowsFromDifferentBlocksWithInnerJSON()
    {
        $rowsBlock1 = '{"date":"2023-01-16","user_id":"100991174365","device_';
        $rowsBlock2 = 'id":"","name":"game_tournament_pictur';
        $rowsBlock3 = 'e_view"}
{"date":"2023-01-16","user_id":"571720';
        $rowsBlock4 = '067392","device_id":"","name":"91pct_user_i';
        $rowsBlock5 = 'nfo"}
{"date":"2023-01-16","user_id":"513942215025","device_id":"","name":"game_t';
        $rowsBlock6 = 'ournament_picture_view"}
{"date":"2023-01-16","user_id":"577298495535","de';
        $rowsBlock7 = 'vice_id":"","name":"100pct_enter_menu"}
{"date":"2023-01-16","user_id":"577298495';
        $rowsBlock8 = '535","device_id":"","name":"98pct_map"}';

        $expectedResult = [
            json_decode('{"date":"2023-01-16","user_id":"100991174365","device_id":"","name":"game_tournament_picture_view"}', true),
            json_decode('{"date":"2023-01-16","user_id":"571720067392","device_id":"","name":"91pct_user_info"}', true),
            json_decode('{"date":"2023-01-16","user_id":"513942215025","device_id":"","name":"game_tournament_picture_view"}', true),
            json_decode('{"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"100pct_enter_menu"}', true),
            json_decode('{"date":"2023-01-16","user_id":"577298495535","device_id":"","name":"98pct_map"}', true),
        ];

        $sut = new JSONEachRowStreamResponseParser();
        $sut->add($rowsBlock1);
        $sut->add($rowsBlock2);
        $sut->add($rowsBlock3);
        $sut->add($rowsBlock4);
        $sut->add($rowsBlock5);
        $sut->add($rowsBlock6);
        $sut->add($rowsBlock7);
        $sut->add($rowsBlock8);

        $actualResult = iterator_to_array($sut->row());
        foreach ($expectedResult as $i => $row) {
            $this->assertArrayHasKey($i, $actualResult);
            $this->assertIsArray($row);
            $this->assertEquals($row, $actualResult[$i]);
        }
    }

}
