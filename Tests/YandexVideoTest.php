<?php

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;


class YandexVideoTest extends \PHPUnit\Framework\TestCase
{
    const SELENIUM_HOST = 'http://localhost:4444/wd/hub';

    const REQUEST_URL = 'https://yandex.ru/video/';

    const SEARCH_STRING = 'ураган';

    const SELECTOR_SEARCH_INPUT = 'div.header span.input__box input.input__control';
    const SELECTOR_SEARCH_BUTTON = 'div.search2__button > button.button';
    const SELECTOR_FIRST_VIDEO_PREVIEW = 'div.serp-controller__content div.serp-item__preview:first-child';
    const SELECTOR_FIRST_VIDEO_IMG_TRAILER = 'div.serp-item__preview:first-child img.thumb-preview__target';

    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    public function setUp()
    {
        $this->webDriver = RemoteWebDriver::create(self::SELENIUM_HOST, DesiredCapabilities::chrome());
    }

    public function testExample()
    {
        $this->webDriver->get(self::REQUEST_URL);

        // Ввод поискового запроса в поле
        $searchInput = $this->webDriver->findElement(WebDriverBy::cssSelector(self::SELECTOR_SEARCH_INPUT));
        $searchInput->sendKeys(self::SEARCH_STRING);

        // Нажать на кнопку поиска
        $searchButton = $this->webDriver->findElement(WebDriverBy::cssSelector(self::SELECTOR_SEARCH_BUTTON));
        $searchButton->click();

        // Подождать пока откроется список видео после поиска
        $this->webDriver->wait(10, 200)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector(self::SELECTOR_FIRST_VIDEO_PREVIEW))
        );
        $firstVideo = $this->webDriver->findElement(WebDriverBy::cssSelector(self::SELECTOR_FIRST_VIDEO_PREVIEW));

        // Переместить курсор на картинку первого видео
        $actions = new WebDriverActions($this->webDriver);
        $actions->moveToElement($firstVideo)->perform();

        $previewsCount = 0;
        $lastPreview = null;
        do {
            // Подождать картинку превью трейлера
            $this->webDriver->wait(2, 200)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector(self::SELECTOR_FIRST_VIDEO_IMG_TRAILER))
            );
            $preview = $this->webDriver->findElement(WebDriverBy::cssSelector(self::SELECTOR_FIRST_VIDEO_IMG_TRAILER));

            // Еще раз переместить курсор на картинку
            $actions->moveToElement($preview)->perform();

            if ($lastPreview && $preview->getAttribute('src') != $lastPreview->getAttribute('src')) {
                $previewsCount++;
            }
            $lastPreview = $preview;

        } while ($previewsCount < 1); // Если есть 2 отличных друг от друга картинки с разным src, то выходим

        $this->assertEquals(1, $previewsCount, 'Трейлер у первого видео не переключается при наведении курсора');
    }

    public function tearDown()
    {
        $this->webDriver->quit();
    }
}
