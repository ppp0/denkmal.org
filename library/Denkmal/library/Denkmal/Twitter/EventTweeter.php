<?php

class Denkmal_Twitter_EventTweeter {

    /** @var Denkmal_Twitter_Client */
    private $_client;

    /** @var  CM_Frontend_Render */
    private $_render;

    /**
     * @param Denkmal_Twitter_Client $client
     * @param CM_Frontend_Render     $render
     */
    public function __construct(Denkmal_Twitter_Client $client, CM_Frontend_Render $render) {
        $this->_client = $client;
        $this->_render = $render;
    }

    /**
     * @param Denkmal_Model_Event $event
     * @param int                 $maxLength
     * @throws CM_Exception_Invalid
     * @return string
     */
    public function getEventText(Denkmal_Model_Event $event, $maxLength) {
        $maxLength = (int) $maxLength;
        $url = $this->_render->getUrlPage('Denkmal_Page_Events', ['date' => $event->getFrom()->format('Y-n-j')]);
        $url = preg_replace('#^https?://(www\.)?#', '', $url);
        $suffix = ' ' . $url;
        $suffixLength = $this->_client->getTweetLength($suffix);
        if ($suffixLength > $maxLength) {
            throw new CM_Exception_Invalid('Suffix length exceeds max-length `' . $maxLength . '`.');
        }

        $prefix = $this->_formatEvent($event);
        $prefixLength = $maxLength - $suffixLength;
        $prefixLengthSubtractor = 0;
        while ($this->_client->getTweetLength($prefix) > $prefixLength) {
            $usertextMaxLength = new CM_Usertext_Usertext($this->_render);
            $usertextMaxLength->addFilter(new CM_Usertext_Filter_MaxLength($prefixLength - $prefixLengthSubtractor));
            $prefix = $usertextMaxLength->transform($prefix);
            $prefixLengthSubtractor++;
        }

        return $prefix . $suffix;
    }

    /**
     * @param Denkmal_Model_Event $event
     * @return string
     */
    private function _formatEvent(Denkmal_Model_Event $event) {
        $html = '';
        $html .= $this->_formatTime($event->getFrom());
        if ($event->getUntil()) {
            $html .= '-' . $this->_formatTime($event->getUntil());
        }
        $html .= ' ';
        $html .= $event->getVenue()->getName();
        $html .= ': ';

        $usertextLinks = new CM_Usertext_Usertext($this->_render);
        $usertextLinks->addFilter(new Denkmal_Usertext_Filter_Links());
        $html .= $usertextLinks->transform($event->getDescription());

        return strip_tags($html);
    }

    /**
     * @param DateTime $date
     * @return string
     */
    private function _formatTime(DateTime $date) {
        $formatter = $this->_render->getFormatterDate(IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'H:mm');
        return $formatter->format($date->getTimestamp());
    }
}
