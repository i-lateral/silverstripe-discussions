<?php

/**
 * A dedicated version of Gridfield Bulk Manager dedicated to approval of user
 * join requests.
 *
 */
class DiscussionGridFieldBulkApprove extends GridFieldBulkManager {

    /**
     * Setup gridfield module
     *
     * @param GridField $gridField
     * @return array
     */
    public function getHTMLFragments($gridField) {
        Requirements::css(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkManager.css');
        Requirements::javascript(BULK_EDIT_TOOLS_PATH . '/javascript/GridFieldBulkManager.js');

        $dropDownActionList = DropdownField::create('bulkActionName', '')
            ->setSource(array(
                'approve' => 'Approve',
                'delete' => 'Reject'
            ));

        $actionButtonHTML = '<a id="doBulkActionButton"';
        $actionButtonHTML .= 'href="'.$gridField->Link('bulkediting').'/edit'.'"';
        $actionButtonHTML .= 'data-url="'.$gridField->Link('bulkediting').'"';
        $actionButtonHTML .= 'class="action ss-ui-button cms-panel-link"';
        $actionButtonHTML .= 'data-icon="pencil">GO</a>';

        $toggleSelectAllHTML = '<span>Select all';
        $toggleSelectAllHTML .= '<input id="toggleSelectAll"';
        $toggleSelectAllHTML .= 'type="checkbox" title="select all"';
        $toggleSelectAllHTML .= 'name="toggleSelectAll"';
        $toggleSelectAllHTML .= '/></span>';

        $html = '<div id="bulkManagerOptions">'.
                $dropDownActionList->FieldHolder().
                $actionButtonHTML.
                $toggleSelectAllHTML.
                '</div>';

        return array(
            'bulk-edit-tools' => $html
        );
    }

    /**
     * Pass control over to the RequestHandler
     *
     * @param GridField $gridField
     * @param SS_HTTPRequest $request
     * @return mixed
     */
    public function handlebulkEdit($gridField, $request) {
        $controller = $gridField
            ->getForm()
            ->Controller();

        $handler = new DiscussionGridFieldBulkApprove_Request(
            $gridField,
            $this,
            $controller
        );

        return $handler->handleRequest($request, DataModel::inst());
    }

}

/**
 * Handle gridfield requests
 *
 * @author Mo
 * @package discussionforum
 */
class DiscussionGridFieldBulkApprove_Request extends GridFieldBulkManager_Request {
    /**
     * Approve membership of selected items
     *
     * @param SS_HTTPRequest $request
     * @return \SS_HTTPResponse
     */
    public function approve(SS_HTTPRequest $request) {
        $recordList = $this->getPOSTRecordList($request);
        $result = array();

        foreach($recordList as $id) {
            $object = DiscussionGroupJoinRequest::get()->byID($id);

            if($object)
                $res = $object->approve();
            else
                $res = false;

            array_push($result, array($id => $res));
        }

        $response = new SS_HTTPResponse(Convert::raw2json(array($result)));
        $response->addHeader('Content-Type', 'text/plain');

        return $response;
    }
}
