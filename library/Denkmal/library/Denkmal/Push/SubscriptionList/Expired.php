<?php

class Denkmal_Push_SubscriptionList_Expired extends Denkmal_Push_SubscriptionList_Abstract {

    /**
     * @param DateTime|null $updatedMax
     */
    public function __construct(DateTime $updatedMax = null) {
        if (null === $updatedMax) {
            $now = new DateTime();
            $updatedMax = $now->sub(new DateInterval('P30D'));
        }

        $where = 'updated < ' . $updatedMax->getTimestamp();
        $source = new CM_PagingSource_Sql('id', Denkmal_Push_Subscription::getTableName(), $where);
        parent::__construct($source);
    }
}
