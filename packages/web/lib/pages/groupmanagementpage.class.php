<?php
/**
 * Group management page
 *
 * PHP version 5
 *
 * @category GroupManagementPage
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * Group management page
 *
 * @category GroupManagementPage
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class GroupManagementPage extends FOGPage
{
    /**
     * Group->Host common items
     *
     * @var array
     */
    private static $_common = array();
    /**
     * The node that uses this class
     *
     * @var string
     */
    public $node = 'group';
    /**
     * Initializes the group management page
     *
     * @param string $name the name to construct under
     *
     * @return void
     */
    public function __construct($name = '')
    {
        $this->name = 'Group Management';
        parent::__construct($this->name);
        global $id;
        if ($id) {
            $this->subMenu = array(
                "$this->linkformat#group-general" =>
                self::$foglang['General'],
                "$this->linkformat#group-image" =>
                self::$foglang['ImageAssoc'],
                "$this->linkformat#group-tasks" =>
                self::$foglang['BasicTasks'],
                "$this->linkformat#group-active-directory" =>
                self::$foglang['AD'],
                "$this->linkformat#group-printers" =>
                self::$foglang['Printers'],
                "$this->linkformat#group-snapins" =>
                self::$foglang['Snapins'],
                "$this->linkformat#group-service" => sprintf(
                    '%s %s',
                    self::$foglang['Service'],
                    self::$foglang['Settings']
                ),
                "$this->linkformat#group-powermanagement" =>
                self::$foglang['PowerManagement'],
                str_replace(
                    'membership',
                    'inventory',
                    $this->membership
                ) => self::$foglang['Inventory'],
                $this->membership => self::$foglang['Membership'],
                $this->delformat => self::$foglang['Delete'],
            );
            $this->notes = array(
                self::$foglang['Group'] => $this->obj->get('name'),
                self::$foglang['Members'] => $this->obj->getHostCount(),
            );
        }
        self::$HookManager->processEvent(
            'SUB_MENULINK_DATA',
            array(
                'menu' => &$this->menu,
                'submenu' => &$this->subMenu,
                'id' => &$this->id,
                'notes' => &$this->notes,
                'object' => &$this->obj,
                'linkformat' => &$this->linkformat,
                'delformat' => &$this->delformat,
                'membership' => &$this->membership
            )
        );
        $this->headerData = array(
            '<input type="checkbox" name="toggle-checkbox" '
            . 'class="toggle-checkboxAction" id="toggler"/>'
            . '<label for="toggler"></label>',
            _('Name'),
            _('Members'),
            _('Tasking'),
        );
        $down = self::getClass('TaskType', 1);
        $mc = self::getClass('TaskType', 8);
        $this->templates = array(
            '<input type="checkbox" name="group[]" '
            . 'value="${id}" class="toggle-action" id="group-${id}"/>'
            . '<label for="group-${id}"></label>',
            sprintf(
                '<a href="?node=group&sub=edit&%s=${id}" '
                . 'title="Edit">${name}</a>',
                $this->id
            ),
            '${count}',
            sprintf(
                '<a href="?node=group&sub=deploy&type=1&%s=${id}">'
                . '<i class="icon fa fa-'
                . $down->get('icon')
                . '" title="'
                . $down->get('name')
                . '"></i></a> <a href="?node=group&sub=deploy&type=8&%s='
                . '${id}"><i class="icon fa fa-'
                . $mc->get('icon')
                . '" title="'
                . $mc->get('name')
                . '"></i></a> <a href="?node=group&sub=edit&%s='
                . '${id}#group-tasks"><i class="icon fa fa-arrows-alt" '
                . 'title="Goto Basic Tasks"></i></a>',
                $this->id,
                $this->id,
                $this->id,
                $this->id,
                $this->id,
                $this->id
            ),
        );
        $this->attributes = array(
            array(
                'width' => 16,
                'class' => 'filter-false'),
            array(),
            array(
                'class' => 'col-xs-4 text-center'),
            array(
                'class' => 'col-xs-7 text-center filter-false'
            ),
        );
        $this->_getHostCommon();
        /**
         * Lamda function to return data either by list or search.
         *
         * @param object $Group the object to use.
         *
         * @return void
         */
        self::$returnData = function (&$Group) {
            $this->data[] = array(
                'id' => $Group->id,
                'name' => $Group->name,
                'description' => $Group->description,
                'count' => $Group->hostcount
            );
            unset($Group);
        };
    }
    /**
     * Create new group
     *
     * @return void
     */
    public function add()
    {
        $this->title = _('New Group');
        unset(
            $this->data,
            $this->form,
            $this->headerData,
            $this->templates,
            $this->attributes
        );
        $this->templates = array(
            '${field}',
            '${input}'
        );
        $this->attributes = array(
            array('class' => 'col-xs-4'),
            array('class' => 'col-xs-8 form-group')
        );
        $fields = array(
            '<label for="name">'
            . _('Group Name')
            . '</label>' => '<div class="input-group has-error">'
            . '<input type="text" name="name" '
            . 'value="'
            . filter_input(INPUT_POST, 'name')
            . '" class="groupname-input form-control" '
            . 'id="name" required/>'
            . '</div>',
            '<label for="description">'
            . _('Group Description')
            . '</label>' => '<div class="input-group">'
            . '<textarea class="form-control" '
            . 'id="description" name="description">'
            . filter_input(INPUT_POST, 'description')
            . '</textarea>'
            . '</div>',
            '<label for="kern">'
            . _('Group Kernel')
            . '</label>' => '<div class="input-group">'
            . '<input type="text" name="kern" '
            . 'value="'
            . filter_input(INPUT_POST, 'kern')
            . '" class="groupkern-input form-control" '
            . 'id="kern"/>'
            . '</div>',
            '<label for="args">'
            . _('Group Kernel Arguments')
            . '</label>' => '<div class="input-group">'
            . '<input type="text" name="args" '
            . 'value="'
            . filter_input(INPUT_POST, 'args')
            . '" class="groupargs-input form-control" '
            . 'id="args"/>'
            . '</div>',
            '<label for="dev">'
            . _('Group Primary Disk')
            . '</label>' => '<div class="input-group">'
            . '<input type="text" name="dev" '
            . 'value="'
            . filter_input(INPUT_POST, 'dev')
            . '" class="groupdev-input form-control" '
            . 'id="dev"/>'
            . '</div>',
            '<label for="addgroup">'
            . _('Make changes?')
            . '</label>' => '<button class="'
            . 'btn btn-info btn-block" type="submit" name='
            . '"addgroup" id="addgroup">'
            . _('Add')
            . '</button>'
        );
        self::$HookManager->processEvent(
            'GROUP_ADD_FIELDS',
            array(
                'fields' => &$fields,
            )
        );
        array_walk($fields, $this->fieldsToData);
        self::$HookManager->processEvent(
            'GROUP_ADD',
            array(
                'headerData' => &$this->headerData,
                'data' => &$this->data,
                'templates' => &$this->templates,
                'attributes' => &$this->attributes
            )
        );
        echo '<div class="col-xs-offset-3">';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo $this->title;
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        echo '<form class="form-horizontal" method="pst" action="'
            . $this->formAction
            . '">';
        $this->render(12);
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    /**
     * When submitted to add post this is what's run
     *
     * @return void
     */
    public function addPost()
    {
        self::$HookManager->processEvent('GROUP_ADD_POST');
        try {
            if (empty($_REQUEST['name'])) {
                throw new Exception('Group Name is required');
            }
            if (self::getClass('GroupManager')->exists($_REQUEST['name'])) {
                throw new Exception('Group Name already exists');
            }
            $Group = self::getClass('Group')
                ->set('name', $_REQUEST['name'])
                ->set('description', $_REQUEST['description'])
                ->set('kernel', $_REQUEST['kern'])
                ->set('kernelArgs', $_REQUEST['args'])
                ->set('kernelDevice', $_REQUEST['dev']);
            if (!$Group->save()) {
                throw new Exception(_('Group create failed'));
            }
            $hook = 'GROUP_ADD_SUCCESS';
            $msg = _('Group added');
        } catch (Exception $e) {
            $hook = 'GROUP_ADD_FAIL';
            $msg = $e->getMessage();
        }
        self::$HookManager->processEvent(
            $hook,
            array('Group' => &$Group)
        );
        unset($Group);
        self::setMessage($msg);
        self::redirect($this->formAction);
    }
    /**
     * Get host common items
     *
     * @return void
     */
    private function _getHostCommon()
    {
        if (count(self::$_common) > 0) {
            return;
        }
        $HostCount = $this->obj->getHostCount();
        $hostids = $this->obj->get('hosts');
        $Host = new Host(@max($hostids));
        $getItems = array(
            'imageID',
            'productKey',
            'printerLevel',
            'useAD',
            'enforce',
            'ADDomain',
            'ADOU',
            'ADUser',
            'ADPass',
            'ADPassLegacy',
            'biosexit',
            'efiexit',
        );
        foreach ($getItems as &$idField) {
            $tmp = self::getClass('HostManager')
                ->distinct(
                    $idField,
                    array('id' => $hostids)
                );
            self::$_common[] = (bool)($tmp == 1);
            unset($idField);
        }
    }
    /**
     * Displays the group general tab.
     *
     * @return void
     */
    public function groupGeneral()
    {
        unset(
            $this->data,
            $this->form,
            $this->headerData,
            $this->attributes,
            $this->templates
        );
        list(
            $imageIDs,
            $groupKey,
            $printerLevel,
            $aduse,
            $enforcetest,
            $adDomain,
            $adOU,
            $adUser,
            $adPass,
            $adPassLegacy,
            $biosExit,
            $efiExit
        ) = self::$_common;
        $hostids = $this->obj->get('hosts');
        $Host = new Host(@max($hostids));
        $exitNorm = Service::buildExitSelector(
            'bootTypeExit',
            (
                $biosExit ?
                $Host->get('biosexit') :
                filter_input(INPUT_POST, 'bootTypeExit')
            ),
            true,
            'bootTypeExit'
        );
        $exitEfi = Service::buildExitSelector(
            'efiBootTypeExit',
            (
                $efiExit ?
                $Host->get('efiexit') :
                filter_input(INPUT_POST, 'efiBootTypeExit')
            ),
            true,
            'efiBootTypeExit'
        );
        $name = (
            filter_input(INPUT_POST, 'name') ?: $this->obj->get('name')
        );
        $desc = (
            filter_input(INPUT_POST, 'description') ?: $this->obj->get('description')
        );
        $productKey = (
            filter_input(INPUT_POST, 'key') ?: $productKey
        );
        $productKey = self::aesdecrypt($productKey);
        $kern = (
            filter_input(INPUT_POST, 'kern') ?: (
                $kern ?: $this->obj->get('kernel')
            )
        );
        $args = (
            filter_input(INPUT_POST, 'args') ?: (
                $args ?: $this->obj->get('kernelArgs')
            )
        );
        $init = (
            filter_input(INPUT_POST, 'init') ?: $init
        );
        $dev = (
            filter_input(INPUT_POST, 'dev') ?: (
                $dev ?: $this->obj->get('kernelDevice')
            )
        );
        $this->attributes = array(
            array('class' => 'col-xs-4'),
            array('class' => 'col-xs-8 form-group')
        );
        $this->templates = array(
            '${field}',
            '${input}'
        );
        $fields = array(
            '<label for="name">'
            . _('Group Name')
            . '</label>' => '<div class="input-group">'
            . '<input type="text" name="name" value="'
            . $name
            . '" class="groupname-input form-control" '
            . 'id="name" required/>'
            . '</div>',
            '<label for="description">'
            . _('Group Description')
            . '</label>' => '<div class="input-group">'
            . '<textarea class="form-control" id="description" '
            . 'name="description">'
            . $desc
            . '</textarea>'
            . '</div>',
            '<label for="productKey">'
            . _('Group Product Key')
            . '</label>' => '<div class="input-group">'
            . '<input id="productKey" type="text" '
            . 'name="key" value="'
            . $productKey
            .'" class="form-control"/>'
            . '</div>',
            '<label for="kern">'
            . _('Group Kernel')
            . '</label>' => '<div class="input-group">'
            . '<input type="text" name="kern" id="kern" '
            . 'class="form-control" value="'
            . $kern
            . '"/>'
            . '</div>',
            '<label for="args">'
            . _('Group Kernel Arguments')
            . '</label>' =>'<div class="input-group">'
            . '<input type="text" name="args" id="args" '
            . 'class="form-control" value="'
            . $args
            . '"/>'
            . '</div>',
            '<label for="dev">'
            . _('Group Primary Disk')
            . '</label>' => '<div class="input-group">'
            . '<input type="text" name="dev" id="dev" '
            . 'class="form-control" value="'
            . $dev
            . '"/>'
            . '</div>',
            '<label for="bootTypeExit">'
            . _('Group Bios Exit Type')
            . '</label>' => $exitNorm,
            '<label for="efiBootTypeExit">'
            . _('Group EFI Exit Type')
            . '</label>' => $exitEfi,
            '<label for="generalupdate">'
            . _('Make Changes?')
            . '</label>' => '<button type="submit" class="btn btn-info btn-block" '
            . 'id="generalupdate">'
            . _('Update')
            . '</button>'
        );
        self::$HookManager->processEvent(
            'GROUP_FIELDS',
            array(
                'fields' => &$fields,
                'Group' => &$this->obj
            )
        );
        array_walk($fields, $this->fieldsToData);
        self::$HookManager
            ->processEvent(
                'GROUP_EDIT_GEN',
                array(
                    'headerData' => &$this->headerData,
                    'data' => &$this->data,
                    'templates' => &$this->templates,
                    'attributes' => &$this->attributes,
                    'Group' => &$this->obj
                )
            );
        $this->form = '<div class="text-center" id="resetSecDataBox">'
            . '<button type="button" '
            . 'id="resetSecData" '
            . 'class="btn btn-warning btn-block">'
            . _('Reset Encryption Data')
            . '</button>'
            . '</div>';
        echo '<!-- General -->';
        echo '<div id="group-general" class="'
            . 'tab-pane fade in active">';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Group general');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        echo '<form class="form-horizontal" method="post" action="'
            . $this->formAction
            . '&tab=group-general">';
        $this->render(12);
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        unset(
            $this->data,
            $this->form,
            $this->headerData,
            $this->attributes,
            $this->templates
        );
    }
    /**
     * Prints the group image element.
     *
     * @return void
     */
    public function groupImage()
    {
        // Group Images
        unset(
            $this->data,
            $this->form,
            $this->headerData,
            $this->templates,
            $this->attributes
        );
        $imageSelector = self::getClass('ImageManager')
            ->buildSelectBox($imageMatchID, 'image');
        $this->attributes = array(
            array('class' => 'col-xs-4'),
            array('class' => 'col-xs-8 form-group'),
        );
        $this->templates = array(
            '${field}',
            '${input}',
        );
        $fields = array(
            '<label for="image">'
            . _('Group image')
            . '</label>' => $imageSelector,
            '<label for="updateimage">'
            . _('Make Changes?')
            . '</label>' => '<button type="submit" class="btn btn-info btn-block" '
            . 'id="updateimage">'
            . _('Update')
            . '</button>'
        );
        self::$HookManager
            ->processEvent(
                'GROUP_IMAGE_FIELDS',
                array(
                    'fields' => &$fields,
                    'Group' => &$this->obj
                )
            );
        array_walk($fields, $this->fieldsToData);
        self::$HookManager
            ->processEvent(
                'GROUP_EDIT_IMAGE',
                array(
                    'data' => &$this->data,
                    'headerData' => &$this->headerData,
                    'templates' => &$this->templates,
                    'attributes' => &$this->attributes
                )
            );
        echo '<!-- Image association -->';
        echo '<div id="group-image" class="tab-pane fade">';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Group image association');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        echo '<form class="form-horizontal" method="post" action="'
            . $this->formAction
            . '&tab=group-image">';
        $this->render(12);
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        unset(
            $this->data,
            $this->form,
            $this->headerData,
            $this->templates,
            $this->attributes
        );
    }
    /**
     * Display the group PM stuff.
     *
     * @return void
     */
    public function groupPMDisplay()
    {
        unset(
            $this->data,
            $this->form,
            $this->headerData,
            $this->templates,
            $this->attributes
        );
        echo '<!-- Power Management Items -->';
        echo '<div class="tab-pane fade" id="group-powermanagement">';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Power Management');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        $this->newPMDisplay();
        unset(
            $this->data,
            $this->form,
            $this->headerData,
            $this->templates,
            $this->attributes
        );
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Group Power Management Remove');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        echo '<label for="delAllPM" class="col-xs-4">'
            . _('Delete all PM tasks?')
            . '</label>';
        echo '<div class="col-xs-8">';
        echo '<button id="delAllPM" type="button" class='
            . '"btn btn-danger btn-block">'
            . _('Delete')
            . '</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        unset(
            $this->data,
            $this->form,
            $this->headerData,
            $this->templates,
            $this->attributes
        );
    }
    /**
     * Displays group printer element.
     *
     * @return void
     */
    public function groupPrinters()
    {
        unset(
            $this->headerData,
            $this->templates,
            $this->attributes,
            $this->form,
            $this->data
        );
        list(
            $imageIDs,
            $groupKey,
            $printerLevel,
            $aduse,
            $enforcetest,
            $adDomain,
            $adOU,
            $adUser,
            $adPass,
            $adPassLegacy,
            $biosExit,
            $efiExit
        ) = self::$_common;
        $this->headerData = array(
            '<label for="toggler1">'
            . '<input type="checkbox" name="toggle-checkboxprint" id="toggler1" '
            . 'class="toggle-checkboxprint"/>'
            . '</label>',
            '',
            _('Printer Alias'),
            _('Printer Type')
        );
        $this->templates = array(
            '<label for="printer-${printer_id}">'
            . '<input type="checkbox" name="printers[]" class='
            . '"toggle-print" id="printer-${printer_id}" '
            . 'value="${printer_id}"/>'
            . '</label>',
            '<div class="radio">'
            . '<input type="radio" class="default" '
            . 'name="default" id="printer${printer_id}" value="${printer_id}"/>'
            . '<label for="printer${printer_id}"></label>'
            . '</div>',
            '<a href="?node=printer&sub=edit&id=${printer_id}">${printer_name}</a>',
            '${printer_type}'
        );
        $this->attributes = array(
            array(
                'class' => 'filter-false col-xs-1'
            ),
            array(
                'class' => 'filter-false col-xs-1'
            ),
            array(),
            array()
        );
        Route::listem('printer');
        $Printers = json_decode(
            Route::getData()
        );
        $Printers = $Printers->printers;
        foreach ((array)$Printers as &$Printer) {
            $this->data[] = array(
                'printer_id' => $Printer->id,
                'printer_name' => $Printer->name,
                'printer_type' => (
                    stripos($Printer->config, 'local') !== false ?
                    _('TCP/IP') :
                    $Printer->config
                )
            );
            unset($Printer);
        }
        self::$HookManager->processEvent(
            'GROUP_PRINTER',
            array(
                'data' => &$this->data,
                'templates' => &$this->templates,
                'headerData' => &$this->headerData,
                'attributes' => &$this->attributes,
            )
        );
        echo '<!-- Printers -->';
        echo '<div class="tab-pane fade" id="group-printers">';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Group Printers');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        echo '<form class="form-horizontal" method="post" action="'
            . $this->formAction
            . '&tab=group-printers">';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Printer Configuration');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        echo '<h5 class="title text-center">';
        echo _('Select management level for these hosts');
        echo '</h5>';
        echo '<div class="col-xs-offset-4">';
        echo '<div class="radio">';
        echo '<label for="nolevel" data-toggle="tooltip" data-placement="left" '
            . 'title="'
            . _('This setting turns off all FOG Printer Management')
            . '. '
            . _('Although there are multiple levels already')
            . ' '
            . _('between host and global settings')
            . ', '
            . _('this is just another to ensure safety')
            . '.">';
        echo '<input type="radio" name="level" value="0" id="nolevel"'
            . (
                $printerLevel == 1 ?
                ' checked' :
                ''
            )
            . '/>';
        echo _('No Printer Management');
        echo '</label>';
        echo '</div>';
        echo '<div class="radio">';
        echo '<label for="addlevel" data-toggle="tooltip" data-placement="left" '
            . 'title="'
            . _(
                'This setting only adds and removes '
                . 'printers that are managed by FOG. '
                . 'If the printer exists in printer '
                . 'management but is not assigned to a '
                . 'host, it will remove the printer if '
                . 'it exists on the unassigned host. '
                . 'It will add printers to the host '
                . 'that are assigned.'
            )
            . '">';
        echo '<input type="radio" name="level" value="1" id="addlevel"'
            . (
                $printerLevel == 1 ?
                ' checked' :
                ''
            )
            . '/>';
        echo _('FOG Managed Printers');
        echo '</label>';
        echo '</div>';
        echo '<div class="radio">';
        echo '<label for="alllevel" data-toggle="tooltip" data-placement="left" '
            . 'title="'
            . _(
                'This setting will only allow FOG Assigned '
                . 'printers to be added to the host. Any '
                . 'printer that is not assigned will be '
                . 'removed including non-FOG managed printers.'
            )
            . '">';
        echo '<input type="radio" name="level" value="2" id="alllevel"'
            . (
                $printerLevel == 2 ?
                ' checked' :
                ''
            )
            . '/>';
        echo _('Only Assigned Printers');
        echo '</label>';
        echo '</div>';
        echo '</div>';
        echo '<br/>';
        echo '<div class="form-group">';
        echo '<label for="levelup" class="control-label col-xs-4">';
        echo _('Update printer configuration');
        echo '</label>';
        echo '<div class="col-xs-8">';
        echo '<button type="submit" name="levelup" class='
            . '"btn btn-info btn-block" id="levelup">'
            . _('Update')
            . '</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Printer Associations');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        $this->render(12);
        echo '</div>';
        echo '</div>';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Make Printer Changes');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        echo '<div class="form-group">';
        echo '<label class="control-label col-xs-4" for="add">';
        echo _('Add Printers');
        echo '</label>';
        echo '<div class="col-xs-8">';
        echo '<button type="submit" name="add" class='
            . '"btn btn-info btn-block" id="add">'
            . _('Add')
            . '</button>';
        echo '</div>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label class="control-label col-xs-4" for="update">';
        echo _('Update Default Printer');
        echo '</label>';
        echo '<div class="col-xs-8">';
        echo '<button type="submit" name="update" class='
            . '"btn btn-info btn-block" id="update">'
            . _('Update')
            . '</button>';
        echo '</div>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label class="control-label col-xs-4" for="remove">';
        echo _('Remove selected printers');
        echo '</label>';
        echo '<div class="col-xs-8">';
        echo '<button type="submit" name="remove" class='
            . '"btn btn-danger btn-block" id="remove">'
            . _('Remove')
            . '</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        unset(
            $this->headerData,
            $this->templates,
            $this->attributes,
            $this->form,
            $this->data
        );
    }
    /**
     * Display Group Snapin element.
     *
     * @return void
     */
    public function groupSnapins()
    {
        unset(
            $this->headerData,
            $this->templates,
            $this->attributes,
            $this->form,
            $this->data
        );
        $this->headerData = array(
            '<label for="toggler2">'
            . '<input type="checkbox" name="toggle-checkboxsnapin" class='
            . '"toggle-checkboxsnapin" id="toggler2"/>'
            . '</label>',
            _('Snapin Name'),
            _('Snapin Created')
        );
        $this->templates = array(
            '<label for="snapin-${snapin_id}">'
            . '<input type="checkbox" name="snapin[]" class='
            . '"toggle-checkboxsnapin" id="toggler2" '
            . 'value="${snapin_id}"/>'
            . '</label>',
            '<a href="?node=snapin&sub=edit&id=${snapin_id}">${snapin_name}</a>',
            '${snapin_created}'
        );
        $this->attributes = array(
            array('class' => 'filter-false col-xs-1'),
            array(),
            array()
        );
        Route::listem('snapin');
        $Snapins = json_decode(
            Route::getData()
        );
        $Snapins = $Snapins->snapins;
        foreach ((array)$Snapins as &$Snapin) {
            $this->data[] = array(
                'snapin_id' => $Snapin->id,
                'snapin_name' => $Snapin->name,
                'snapin_created' => self::niceDate(
                    $Snapin->createdTime
                )->format('Y-m-d H:i:s')
            );
            unset($Snapin);
        }
        self::$HookManager->processEvent(
            'GROUP_SNAPINS',
            array(
                'data' => &$this->data,
                'templates' => &$this->templates,
                'headerData' => &$this->headerData,
                'attributes' => &$this->attributes,
            )
        );
        echo '<!-- Snapins -->';
        echo '<div class="tab-pane fade" id="group-snapins">';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Group Snapins');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        echo '<form class="form-horizontal" method="post" action="'
            . $this->formAction
            . '&tab=group-snapins">';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Available Snapins');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        $this->render(12);
        echo '</div>';
        echo '</div>';
        echo '<div class="panel panel-info">';
        echo '<div class="panel-heading text-center">';
        echo '<h4 class="title">';
        echo _('Make Snapin Changes');
        echo '</h4>';
        echo '</div>';
        echo '<div class="panel-body">';
        echo '<div class="form-group">';
        echo '<label for="addsnapins" class="control-label col-xs-4">';
        echo _('Add selected snapins');
        echo '</label>';
        echo '<div class="col-xs-8">';
        echo '<button type="submit" name="add" class='
            . '"btn btn-info btn-block" id="addsnapins">';
        echo _('Add');
        echo '</button>';
        echo '</div>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label for="remsnapins" class="control-label col-xs-4">';
        echo _('Remove selected snapins');
        echo '</label>';
        echo '<div class="col-xs-8">';
        echo '<button type="submit" name="remove" class='
            . '"btn btn-danger btn-block" id="remsnapins">';
        echo _('Remove');
        echo '</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        unset(
            $this->headerData,
            $this->templates,
            $this->attributes,
            $this->form,
            $this->data
        );
    }
    /**
     * The group edit display method
     *
     * @return void
     */
    public function edit()
    {
        list(
            $imageIDs,
            $groupKey,
            $printerLevel,
            $aduse,
            $enforcetest,
            $adDomain,
            $adOU,
            $adUser,
            $adPass,
            $adPassLegacy,
            $biosExit,
            $efiExit
        ) = self::$_common;
        $hostids = $this->obj->get('hosts');
        $Host = new Host(@max($hostids));
        // Set Field Information
        $printerLevel = (
            $printerLevel ?
            $Host->get('printerLevel') :
            ''
        );
        $imageMatchID = (
            $imageIDs ?
            $Host->get('imageID') :
            ''
        );
        $useAD = (
            $aduse ?
            $Host->get('useAD') :
            ''
        );
        $enforce = (
            $enforcetest ?
            $Host->get('enforce') :
            ''
        );
        $ADDomain = (
            $adDomain ?
            $Host->get('ADDomain') :
            ''
        );
        $ADOU = (
            $adOU ?
            $Host->get('ADOU') :
            ''
        );
        $ADUser = (
            $adUser ?
            $Host->get('ADUser') :
            ''
        );
        $adPass = (
            $adPass ?
            $Host->get('ADPass') :
            ''
        );
        $ADPass = self::encryptpw($Host->get('ADPass'));
        $ADPassLegacy = (
            $adPassLegacy ?
            $Host->get('ADPassLegacy') :
            ''
        );
        echo '<div class="col-xs-9 tab-content">';
        $this->groupGeneral();
        $this->groupImage();
        $this->basictasksOptions();
        $this->adFieldsToDisplay(
            $useAD,
            $ADDomain,
            $ADOU,
            $ADUser,
            $ADPass,
            $ADPassLegacy,
            $enforce
        );
        $this->groupPrinters();
        $this->groupSnapins();
        echo '<!-- Service Settings --><div id="group-service" class="'
            . 'tab-pane fade">';
        $this->attributes = array(
            array('width'=>270),
            array(),
            array(),
        );
        $this->templates = array(
            '${mod_name}',
            '${input}',
            '${span}',
        );
        $this->data[] = array(
            'mod_name' => 'Select/Deselect All',
            'input' => '<input type="checkbox" class="checkboxes" '
            . 'id="checkAll" name="checkAll" value="checkAll"/>'
            . '<label for="checkAll"></label>',
            'span' => '&nbsp;',
        );
        printf(
            '<h2>%s</h2><form method="post" action="%s&tab=group-service">'
            . '<fieldset><legend>%s</legend>',
            _('Service Configuration'),
            $this->formAction,
            _('General')
        );
        $dcnote = sprintf(
            '%s. %s %s. %s %s.',
            _('This module is only used on the old client'),
            _('The old client is what was distributed with'),
            _('FOG 1.2.0 and earlier'),
            _('This module did not work past Windows XP due'),
            _('to UAC introduced in Vista and up')
        );
        $gfnote = sprintf(
            '%s. %s %s. %s %s %s. %s.',
            _('This module is only used on the old client'),
            _('The old client is what was distributed'),
            _('with FOG 1.2.0 and earlier'),
            _('This module has been replaced in the new client'),
            _('and the equivalent module for what Green FOG'),
            _('did is now called Power Management'),
            _('This is only here to maintain old client operations')
        );
        $ucnote = sprintf(
            '%s. %s %s. %s %s.',
            _('This module is only used on the old client'),
            _('The old client is what was distributed with'),
            _('FOG 1.2.0 and earlier'),
            _('This module did not work past Windows XP due'),
            _('to UAC introduced in Vista and up')
        );
        $cunote = sprintf(
            '%s (%s) %s.',
            _('This module is only used'),
            _('with modules and config'),
            _('on the old client')
        );
        $moduleName = self::getGlobalModuleStatus();
        $ModuleOn = array_values(
            self::getSubObjectIDs(
                'ModuleAssociation',
                array(
                    'hostID' => $this->obj->get('hosts')
                ),
                'moduleID',
                false,
                'AND',
                'id',
                false,
                ''
            )
        );
        foreach ((array)self::getClass('ModuleManager')
            ->find() as &$Module
        ) {
            $note = '';
            switch ($Module->get('shortName')) {
            case 'dircleanup':
                $note = sprintf(
                    '<i class="icon fa fa-exclamation-triangle '
                    . 'fa-1x hand" title="%s"></i>',
                    $dcnote
                );
                break;
            case 'greenfog':
                $note = sprintf(
                    '<i class="icon fa fa-exclamation-triangle fa-1x '
                    . 'hand" title="%s"></i>',
                    $gfnote
                );
                break;
            case 'usercleanup':
                $note = sprintf(
                    '<i class="icon fa fa-exclamation-triangle fa-1x '
                    . 'hand" title="%s"></i>',
                    $ucnote
                );
                break;
            case 'clientupdater':
                $note = sprintf(
                    '<i class="icon fa fa-exclamation-triangle fa-1x '
                    . 'hand" title="%s"></i>',
                    $cunote
                );
                break;
            default:
                $note = '';
                break;
            }
            $this->data[] = array(
                'input' => sprintf(
                    '<input id="%s" %stype="checkbox" name="modules[]" '
                    . 'value="%s"%s%s/><label for="%s"></label>',
                    $Module->get('shortName'),
                    (
                        $moduleName[$Module->get('shortName')]
                        || (
                            $moduleName[$Module->get('shortName')]
                            && $Module->get('isDefault')
                        ) ?
                        'class="checkboxes" ':
                        ''
                    ),
                    $Module->get('id'),
                    (
                        count(
                            array_keys(
                                $ModuleOn,
                                $Module->get('id')
                            )
                        ) == $HostCount ?
                        ' checked' :
                        ''
                    ),
                    (
                        !$moduleName[$Module->get('shortName')] ?
                        ' disabled' :
                        ''
                    ),
                    $Module->get('shortName')
                ),
                'span' => sprintf(
                    '%s<span class="icon fa fa-question fa-1x hand" '
                    . 'title="%s"></span>',
                    $note,
                    str_replace('"', '\"', $Module->get('description'))
                ),
                'mod_name' => $Module->get('name'),
            );
            unset($Module);
        }
        unset($moduleName, $ModuleOn);
        $this->data[] = array(
            'mod_name' => '',
            'input' => '',
            'span' => sprintf(
                '<input type="submit" name="updatestatus" value="%s"/>',
                _('Update')
            ),
        );
        self::$HookManager->processEvent(
            'GROUP_MODULES',
            array(
                'headerData' => &$this->headerData,
                'data' => &$this->data,
                'templates' => &$this->templates,
                'attributes' => &$this->attributes
            )
        );
        $this->render();
        unset($this->data);
        printf(
            '</fieldset><fieldset><legend>%s</legend>',
            _('Group Screen Resolution')
        );
        $this->attributes = array(
            array(),
            array(),
            array(),
        );
        $this->templates = array(
            '${field}',
            '${input}',
            '${span}',
        );
        $find = array(
            'name' => array(
                'FOG_CLIENT_DISPLAYMANAGER_X',
                'FOG_CLIENT_DISPLAYMANAGER_Y',
                'FOG_CLIENT_DISPLAYMANAGER_R',
            )
        );
        foreach ((array)self::getClass('ServiceManager')
            ->find(
                $find,
                'OR',
                'id'
            ) as $Service
        ) {
            switch ($Service->get('name')) {
            case 'FOG_CLIENT_DISPLAYMANAGER_X':
                $name = 'x';
                $field = _('Screen Width (in pixels)');
                break;
            case 'FOG_CLIENT_DISPLAYMANAGER_Y':
                $name = 'y';
                $field = _('Screen Height (in pixels)');
                break;
            case 'FOG_CLIENT_DISPLAYMANAGER_R':
                $name = 'r';
                $field = _('Screen Refresh Rate (in Hz)');
                break;
            }
            $this->data[] = array(
                'input' => sprintf(
                    '<input type="text" name="%s" value="%s"/>',
                    $name,
                    $Service->get('value')
                ),
                'span' => sprintf(
                    '<span class="icon fa fa-question fa-1x hand" title="%s">'
                    . '</span>',
                    $Service->get('description')
                ),
                'field' => $field,
            );
            unset($name, $field, $Service);
        }
        $this->data[] = array(
            'field'=>'',
            'input'=>'',
            'span'=>sprintf(
                '<input type="submit" name="updatedisplay" value="%s"/>',
                _('Update')
            ),
        );
        self::$HookManager->processEvent(
            'GROUP_DISPLAY',
            array(
                'headerData' => &$this->headerData,
                'data' => &$this->data,
                'templates' => &$this->templates,
                'attributes' => &$this->attributes
            )
        );
        $this->render();
        unset($this->data);
        printf(
            '</fieldset><fieldset><legend>%s</legend>',
            _('Auto Log Out Settings')
        );
        $this->attributes = array(
            array('width'=>270),
            array(),
            array(),
        );
        $this->templates = array(
            '${field}',
            '${input}',
            '${desc}',
        );
        $Service = self::getClass('Service')
            ->set('name', 'FOG_CLIENT_AUTOLOGOFF_MIN')
            ->load('name');
        $this->data[] = array(
            'field' => _('Auto Log Out Time (in minutes)'),
            'input' => sprintf(
                '<input type="text" name="tme" value="%s"/>',
                $Service->get('value')
            ),
            'desc' => sprintf(
                '<span class="icon fa fa-question fa-1x hand" '
                . 'title="%s"></span>',
                $Service->get('description')
            ),
        );
        unset($Service);
        $this->data[] = array(
            'field' => '',
            'input' => '',
            'desc' => sprintf(
                '<input type="submit" name="updatealo" value="%s"/>',
                _('Update')
            ),
        );
        self::$HookManager->processEvent(
            'GROUP_ALO',
            array(
                'headerData' => &$this->headerData,
                'data' => &$this->data,
                'templates' => &$this->templates,
                'attributes' => &$this->attributes
            )
        );
        $this->render();
        unset($this->data);
        echo '</fieldset></form></div>';
        $this->groupPMDisplay();
        unset(
            $imageID,
            $imageMatchID,
            $groupKey,
            $groupKeyMatch,
            $aduse,
            $adDomain,
            $adOU,
            $adUser,
            $adPass,
            $adPassLegacy,
            $useAD,
            $ADOU,
            $ADDomain,
            $ADUser,
            $adPass,
            $ADPass,
            $ADPassLegacy,
            $biosExit,
            $efiExit,
            $exitNorm,
            $exitEfi
        );
    }
    /**
     * Display inventory page, separated as groups can contain
     * a lot of information
     *
     * @return void
     */
    public function inventory()
    {
        $this->title = sprintf(
            '%s %s',
            _('Group'),
            self::$foglang['Inventory']
        );
        printf(
            $this->reportString,
            sprintf(
                'Group_%s_InventoryReport',
                $this->obj->get('name')
            ),
            _('Export CSV'),
            _('Export CSV'),
            self::$csvfile,
            sprintf(
                'Group_%s_InventoryReport',
                $this->obj->get('name')
            ),
            _('Export PDF'),
            _('Export PDF'),
            self::$pdffile
        );
        $this->ReportMaker = self::getClass('ReportMaker');
        foreach (self::$inventoryCsvHead as $csvHeader => &$classGet) {
            $this->ReportMaker->addCSVCell($csvHeader);
            unset($classGet, $csvHeader);
        }
        $this->ReportMaker->endCSVLine();
        $this->headerData = array(
            _('Host name'),
            _('Memory'),
            _('System Product'),
            _('System Serial'),
        );
        $this->templates = array(
            '${host_name}<br/><small>${host_mac}</small>',
            '${memory}',
            '${sysprod}',
            '${sysser}',
        );
        $this->attributes = array(
            array(),
            array(),
            array(),
            array(),
        );
        foreach ((array)self::getClass('HostManager')
            ->find(
                array('id' => $this->obj->get('hosts'))
            ) as &$Host
        ) {
            if (!$Host->get('inventory')->isValid()) {
                continue;
            }
            $Image = $Host->getImage();
            $this->data[] = array(
                'host_name' => $Host->get('name'),
                'host_mac' => $Host->get('mac'),
                'memory' => $Host->get('inventory')->getMem(),
                'sysprod' => $Host->get('inventory')->get('sysproduct'),
                'sysser' => $Host->get('inventory')->get('sysserial'),
            );
            foreach (self::$inventoryCsvHead as $csvHead => &$classGet) {
                switch ($csvHead) {
                case _('Host ID'):
                    $this->ReportMaker->addCSVCell(
                        $Host->get('id')
                    );
                    break;
                case _('Host name'):
                    $this->ReportMaker->addCSVCell(
                        $Host->get('name')
                    );
                    break;
                case _('Host MAC'):
                    $this->ReportMaker->addCSVCell(
                        $Host->get('mac')
                    );
                    break;
                case _('Host Desc'):
                    $this->ReportMaker->addCSVCell(
                        $Host->get('description')
                    );
                    break;
                case _('Host Memory'):
                    $this->ReportMaker->addCSVCell(
                        $Host->get('inventory')->getMem()
                    );
                    break;
                default:
                    $this->ReportMaker->addCSVCell(
                        $Host->get('inventory')->get($classGet)
                    );
                    break;
                }
                unset($classGet, $csvHead);
            }
            $this->ReportMaker->endCSVLine();
            unset($Host, $index);
        }
        unset($Hosts);
        $this->ReportMaker->appendHTML($this->__toString());
        $this->ReportMaker->outputReport(false);
        $_SESSION['foglastreport'] = serialize($this->ReportMaker);
        echo '</div>';
    }
    /**
     * Submit the edit function.
     *
     * @return void
     */
    public function editPost()
    {
        self::$HookManager
            ->processEvent(
                'GROUP_EDIT_POST',
                array('Group' => &$this->obj)
            );
        try {
            $hostids = $this->obj->get('hosts');
            switch ($_REQUEST['tab']) {
            case 'group-general':
                if (empty($_REQUEST['name'])) {
                    throw new Exception(_('Group Name is required'));
                }
                $this->obj
                    ->set('name', $_REQUEST['name'])
                    ->set('description', $_REQUEST['description'])
                    ->set('kernel', $_REQUEST['kern'])
                    ->set('kernelArgs', $_REQUEST['args'])
                    ->set('kernelDevice', $_REQUEST['dev']);
                $productKey = preg_replace(
                    '/([\w+]{5})/',
                    '$1-',
                    str_replace(
                        '-',
                        '',
                        strtoupper(
                            trim(
                                $_REQUEST['key']
                            )
                        )
                    )
                );
                $productKey = substr($productKey, 0, 29);
                self::getClass('HostManager')
                    ->update(
                        array(
                            'id' => $hostids
                        ),
                        '',
                        array(
                            'kernel' => $_REQUEST['kern'],
                            'kernelArgs' => $_REQUEST['args'],
                            'kernelDevice' => $_REQUEST['dev'],
                            'efiexit' => $_REQUEST['efiBootTypeExit'],
                            'biosexit' => $_REQUEST['bootTypeExit'],
                            'productKey' => self::encryptpw(
                                trim(
                                    $_REQUEST['key']
                                )
                            )
                        )
                    );
                break;
            case 'group-image':
                $this->obj->addImage($_REQUEST['image']);
                break;
            case 'group-active-directory':
                $useAD = isset($_REQUEST['domain']);
                $domain = $_REQUEST['domainname'];
                $ou = $_REQUEST['ou'];
                $user = $_REQUEST['domainuser'];
                $pass = $_REQUEST['domainpassword'];
                $legacy = $_REQUEST['domainpasswordlegacy'];
                $enforce = isset($_REQUEST['enforcesel']);
                $this->obj->setAD(
                    $useAD,
                    $domain,
                    $ou,
                    $user,
                    $pass,
                    $legacy,
                    $enforce
                );
                break;
            case 'group-printers':
                if (isset($_REQUEST['add'])) {
                    $this->obj->addPrinter(
                        $_REQUEST['printers'],
                        array(),
                        $_REQUEST['level']
                    );
                    $default = $_REQUEST['default'];
                    $printrs = $_REQUEST['printers'];
                    if (in_array($default, (array)$printrs)) {
                        $this->obj->updateDefault($default);
                    }
                }
                if (isset($_REQUEST['remove'])) {
                    $this->obj->addPrinter(
                        array(),
                        $_REQUEST['printers'],
                        $_REQUEST['level']
                    );
                }
                if (isset($_REQUEST['update'])) {
                    $this->obj->addPrinter(
                        array(),
                        array(),
                        $_REQUEST['level']
                    );
                    $this->obj->addPrinter(
                        $_REQUEST['default'],
                        array(),
                        $_REQUEST['level']
                    );
                    $this->obj->updateDefault($_REQUEST['default']);
                }
                break;
            case 'group-snapins':
                if (isset($_REQUEST['add'])) {
                    $this->obj->addSnapin($_REQUEST['snapin']);
                }
                if (isset($_REQUEST['remove'])) {
                    $this->obj->removeSnapin($_REQUEST['snapin']);
                }
                break;
            case 'group-service':
                list(
                    $time,
                    $r,
                    $x,
                    $y
                ) = self::getSubObjectIDs(
                    'Service',
                    array(
                        'name' => array(
                            'FOG_CLIENT_AUTOLOGOFF_MIN',
                            'FOG_CLIENT_DISPLAYMANAGER_R',
                            'FOG_CLIENT_DISPLAYMANAGER_X',
                            'FOG_CLIENT_DISPLAYMANAGER_Y'
                        )
                    ),
                    'value'
                );
                $x = (
                    is_numeric($_REQUEST['x']) ?
                    $_REQUEST['x'] :
                    $x
                );
                $y = (
                    is_numeric($_REQUEST['y']) ?
                    $_REQUEST['y'] :
                    $y
                );
                $r = (
                    is_numeric($_REQUEST['r']) ?
                    $_REQUEST['r'] :
                    $r
                );
                $time = (
                    is_numeric($_REQUEST['tme']) ?
                    $_REQUEST['tme'] :
                    $time
                );
                $mods = self::getSubObjectIDs('Module');
                $modOn = array_intersect(
                    (array)$mods,
                    (array)$_REQUEST['modules']
                );
                $modOff = array_diff(
                    (array)$mods,
                    (array)$modOn
                );
                $this->obj
                    ->addModule($modOn)
                    ->removeModule($modOff)
                    ->setDisp($x, $y, $r)
                    ->setAlo($time);
                break;
            case 'group-powermanagement':
                $min = $_REQUEST['scheduleCronMin'];
                $hour = $_REQUEST['scheduleCronHour'];
                $dom = $_REQUEST['scheduleCronDOM'];
                $month = $_REQUEST['scheduleCronMonth'];
                $dow = $_REQUEST['scheduleCronDOW'];
                $onDemand = (string)intval(isset($_REQUEST['onDemand']));
                $action = $_REQUEST['action'];
                if (!$action) {
                    throw new Exception(_('You must select an action to perform'));
                }
                $items = array();
                if (isset($_REQUEST['pmsubmit'])) {
                    if ($onDemand && $action === 'wol') {
                        $this->obj->wakeOnLAN();
                        break;
                    }
                    $hostIDs = (array)$this->obj->get('hosts');
                    $items = array();
                    foreach ((array)$hostIDs as &$hostID) {
                        $items[] = array(
                            $hostID,
                            $min,
                            $hour,
                            $dom,
                            $month,
                            $dow,
                            $onDemand,
                            $action
                        );
                        unset($hostID);
                    }
                    $fields = array(
                        'hostID',
                        'min',
                        'hour',
                        'dom',
                        'month',
                        'dow',
                        'onDemand',
                        'action'
                    );
                    if (count($items) > 0) {
                        self::getClass('PowerManagementManager')
                            ->insertBatch($fields, $items);
                    }
                }
                break;
            }
            if (!$this->obj->save()) {
                throw new Exception(_('Database update failed'));
            }
            $hook = 'GROUP_EDIT_SUCCESS';
            $msg = _('Group information updated');
        } catch (Exception $e) {
            $hook = 'GROUP_EDIT_FAIL';
            $msg = $e->getMessage();
        }
        self::$HookManager
            ->processEvent(
                $hook,
                array('Group' => &$this->obj)
            );
        self::setMessage($msg);
        self::redirect($this->formAction);
    }
    /**
     * Delete the hosts with the delete group.
     *
     * @return void
     */
    public function deletehosts()
    {
        $this->title = _('Delete Hosts');
        unset($this->data);
        $this->headerData = array(
            _('Host Name'),
            _('Last Deployed'),
        );
        $this->attributes = array(
            array('class' => 'col-xs-4'),
            array('class' => 'col-xs-8 form-group'),
        );
        $this->templates = array(
            '${host_name}<br/><small>${host_mac}</small>',
            '<small>${host_deployed}</small>',
        );
        $hostids = $this->obj->get('hosts');
        foreach ((array)self::getClass('HostManager')
            ->find(
                array('id' => $hostids)
            ) as &$Host
        ) {
            $this->data[] = array(
                'host_name' => $Host->get('name'),
                'host_mac' => $Host->get('mac'),
                'host_deployed' => self::formatTime(
                    $Host->get('deployed'),
                    'Y-m-d H:i:s'
                ),
            );
            unset($Host);
        }
        printf(
            '<p>%s</p>',
            _('Confirm you really want to delete the following hosts')
        );
        printf(
            '<form method="post" action="?node=group&sub=delete&id=%s" class="c">',
            $this->obj->get('id')
        );
        self::$HookManager
            ->processEvent(
                'GROUP_DELETE_HOST_FORM',
                array(
                    'headerData' => &$this->headerData,
                    'data' => &$this->data,
                    'templates' => &$this->templates,
                    'attributes' => &$this->attributes
                )
            );
        $this->render();
        printf(
            '<input type="submit" name="delHostConfirm" value="%s" /></form>',
            _('Delete listed hosts')
        );
    }
}
