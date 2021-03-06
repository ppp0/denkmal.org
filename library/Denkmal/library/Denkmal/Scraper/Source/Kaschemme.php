<?php

class Denkmal_Scraper_Source_Kaschemme extends Denkmal_Scraper_Source_Abstract {

    public function run(Denkmal_Scraper_Manager $manager) {
        $html = self::loadUrl('http://www.kaschemme.ch/Programm-1');

        return $this->processPage($html);
    }

    /**
     * @param string        $html
     * @param DateTime|null $now
     * @return Denkmal_Scraper_EventData[]
     */
    public function processPage($html, DateTime $now = null) {
        $html = new CM_Dom_NodeList($html, true);
        $eventHtml = $html->find('projectcontent')->getHtml();
        $regexp = '(?<weekday>\w+)\s+(?<day>\d+)\.(?<month>\d+)\.(?<year>\d+)\s*<br>(?<description>.+?)\.{4,}';
        preg_match_all('#' . $regexp . '#u', $eventHtml, $matches, PREG_SET_ORDER);

        return Functional\map($matches, function (array $match) use ($now) {
            $from = new Denkmal_Scraper_Date($match['day'], $match['month'], $match['year'], $now);
            $from->setTime(22);

            $descriptionList = explode('<br>', $match['description']);
            $descriptionList = Functional\map($descriptionList, function ($descriptionItem) {
                return $this->_parseText($descriptionItem);
            });
            $descriptionList = array_filter($descriptionList);

            $genres = null;
            foreach ($descriptionList as $i => $descriptionItem) {
                if (preg_match('#^Sounds Like\s*: \(?(?<genres>.+?)\)?$#ui', $descriptionItem, $matchesGenres)) {
                    $genres = new Denkmal_Scraper_Genres($matchesGenres['genres']);
                    unset($descriptionList[$i]);
                }
                if (preg_match('#Eintritt frei#ui', $descriptionItem, $matchesGenres)) {
                    unset($descriptionList[$i]);
                }
                if (preg_match('#(\b|^)\d{1,2}h(\b|$)#ui', $descriptionItem, $matchesGenres)) {
                    unset($descriptionList[$i]);
                }
            }

            $description = implode(', ', $descriptionList);

            return new Denkmal_Scraper_EventData('Kaschemme', new Denkmal_Scraper_Description($description, null, $genres), $from);
        });
    }

    public function getRegion() {
        return Denkmal_Model_Region::getBySlug('basel');
    }

    /**
     * @param string $text
     * @return string
     */
    private function _parseText($text) {
        $text = html_entity_decode($text);
        $text = preg_replace('#^///\s*#', '', $text);
        $text = preg_replace('#\s*///$#', '', $text);
        $text = trim($text);
        return $text;
    }
}
