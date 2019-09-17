<?php
/**
 * Omnisales © 2018
 *
 */

namespace Omnisales;

use Omnisales\Omnisales;
use Omnisales\OmnisalesEndpoint;

/**
 * Class OmnisalesAPIManager
 *
 * @package Omnisales
 */
class OmnisalesAPIManager {

    protected $MapEndpointApi;

    /** @var self */
    protected static $instance;

    /**
     * Get a singleton instance of the class
     *
     * @return self
     * @codeCoverageIgnore
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->MapEndpointApi = array(
            OmnisalesEndpoint::API_OAUTH_GET_ACCESS_TOKEN => Omnisales::API_TYPE_AUTHEN,
            OmnisalesEndpoint::API_GRAPH_APP_REQUESTS => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_FRIENDS => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_INVITABLE_FRIENDS => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_ME => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_MESSAGE => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_IMAGE => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_CREATE_COMMENT => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_REMOVE_COMMENT => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_HIDE_COMMENT => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_LIKE_COMMENT => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_PRIVATE_REPLIES_COMMENT => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_GRAPH_POST_FEED => Omnisales::API_TYPE_GRAPH,
            OmnisalesEndpoint::API_OA_SEND_FOLLOW_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_GET_LIST_TAG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_REMOVE_TAG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_REMOVE_USER_FROM_TAG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_TAG_USER => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_CREATE_QR_CODE => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_GET_MSG_STATUS => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_GET_PROFILE => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_SEND_CUSTOMER_CARE_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_SEND_CUSTOMER_CARE_MSG_BY_PHONE => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_REPLY_LINK_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_REPLY_PHOTO_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_REPLY_TEXT_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_SEND_ACTION_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_SEND_GIF_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_SEND_LINK_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_SEND_PHOTO_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_SEND_STICKER_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_SEND_TEXT_MSG => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_UPLOAD_GIF => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_UPLOAD_PHOTO => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_CREATE_ATTRIBUTE => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_UPDATE_ATTRIBUTE => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_GET_SLICE_ATTRIBUTE => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_GET_SLICE_ATTRIBUTE_TYPE => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_UPDATE_VARIATION => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_ADD_VARIATION => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_GET_ATTRIBUTE_INFO => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_CREATE_PRODUCT => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_GET_ORDER => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_GET_PRODUCT => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_GET_SLICE_CATEGORY => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_GET_SLICE_ORDER => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_GET_SLICE_PRODUCT => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_REMOVE_PRODUCT => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_UPDATE_CATEGORY => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_UPDATE_ORDER => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_UPDATE_PRODUCT => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_UPDATE_SHOP => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_UPLOAD_CATEGORY_PHOTO => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_STORE_UPLOAD_PRODUCT_PHOTO => Omnisales::API_TYPE_OA,

            OmnisalesEndpoint::API_GET_PROFILE => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_GET_PAGES => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_GET_POSTS => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_GET_POST => Omnisales::API_TYPE_OA_ONBEHALF,

            OmnisalesEndpoint::API_EVENT_UPDATE_LIVECHAT_REMOTE => Omnisales::API_TYPE_OA_ONBEHALF,

            OmnisalesEndpoint::API_OA_ONBEHALF_CONVERSATION => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_GET_OA => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_GET_PROFILE => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_RECENT_CHAT => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_REPLY_LINK_MSG => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_REPLY_PHOTO_MSG => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_REPLY_TEXT_MSG => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_SEND_ACTION_MSG => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_SEND_GIF_MSG => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_SEND_LINK_MSG => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_SEND_PHOTO_MSG => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_SEND_STICKER_MSG => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_SEND_TEXT_MSG => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_UPLOAD_GIF => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_ONBEHALF_UPLOAD_PHOTO => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_CREATE_CATEGORY => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_CREATE_PRODUCT => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_GET_ORDER => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_GET_PRODUCT => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_GET_SLICE_CATEGORY => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_GET_SLICE_ORDER => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_GET_SLICE_PRODUCT => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_REMOVE_PRODUCT => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_UPDATE_CATEGORY => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_UPDATE_ORDER => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_UPDATE_PRODUCT => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_UPDATE_SHOP => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_UPLOAD_CATEGORY_PHOTO => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::API_OA_STORE_ONBEHALF_UPLOAD_PRODUCT_PHOTO => Omnisales::API_TYPE_OA_ONBEHALF,
            OmnisalesEndpoint::UPLOAD_VIDEO_URL => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_UPLOAD_VIDEO => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_GET_VIDEO_ID => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_GET_VIDEO_STATUS => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_CREATE_MEDIA => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_GET_MEDIA_ID => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_UPDATE_MEDIA => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_REMOVE_MEDIA => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_GET_SLICE_MEDIA => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_BROADCAST_MEDIA => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_CREATE_VIDEO => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_UPDATE_VIDEO => Omnisales::API_TYPE_OA,
            OmnisalesEndpoint::API_OA_ARTICLE_GET_SLICE_VIDEO => Omnisales::API_TYPE_OA
        );
    }

    public function getMapEndPoint() {
        return $this->MapEndpointApi;
    }

}
