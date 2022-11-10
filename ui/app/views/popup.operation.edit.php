<?php declare(strict_types = 0);
/*
** Zabbix
** Copyright (C) 2001-2022 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * @var CView $this
 * @var array $data
 */

$form = (new CForm())
	->cleanItems()
	->setId('popup.operation')
	->setName('popup.operation')
	->addVar('operation[eventsource]', $data['eventsource'])
	->addVar('operation[recovery]', $data['recovery'])
	->addItem((new CInput('submit', 'submit'))->addStyle('display: none;'));

$form_grid = (new CFormGrid());
$operation = $data['operation'];

$operationtype = array_key_exists('operationtype', $operation)
	? $operation['operationtype']
	: '0';

$operationtype_value = $operation['opcommand']['scriptid'] !== '0'
	? 'scriptid['.$operation['opcommand']['scriptid'].']'
	: 'cmd['.$operationtype.']';

// Operation type row.
if (count($data['operation_types']) > 1) {
$select_operationtype = (new CSelect(''))
	->setFocusableElementId('operationtype')
	->addOptions(CSelect::createOptionsFromArray($data['operation_types']))
	->setAttribute('value', $operationtype_value ?? 0)
	->setId('operation-type-select')
	->setName('operation[operationtype]');

	$form_grid->addItem([
		(new CLabel(_('Operation'), $select_operationtype
			->getFocusableElementId()))->setId('operation-type-label'),
		(new CFormField($select_operationtype))
			->setId('operation-type')
	]);
}
else {
	$form_grid->addItem([
		(new CLabel(_('Operation'), 'operation-type'))->setId('operation-type-label'),
		(new CFormField([
			new CLabel($data['operation_types']),
			(new CInput('hidden', $data['operation_types']))
				->setId('operation-type-select')
				->setAttribute('value', $operationtype_value)
				->setName('operation[operationtype]')
		]))
			->setId('operation-type')
	]);
}

// Operation escalation steps row.
$step_from = (new CNumericBox('operation[esc_step_from]', 1, 5))
	->setAttribute('value', $operation['esc_step_from'] ?? 1)
	->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH);
$step_from->onChange($step_from->getAttribute('onchange').' if (this.value < 1) this.value = 1;');

if (($data['eventsource'] == EVENT_SOURCE_TRIGGERS || $data['eventsource'] == EVENT_SOURCE_INTERNAL ||
		$data['eventsource'] == EVENT_SOURCE_SERVICE) && $data['recovery'] == ACTION_OPERATION) {
	$form_grid->addItem([
		(new CLabel(_('Steps'), 'operation_esc_step_from'))->setId('operation-step-range-label'),
		(new CFormField([
			$step_from->setId('operation_esc_step_from'),
			(new CDiv())->addClass(ZBX_STYLE_FORM_INPUT_MARGIN), '-',
			(new CDiv())->addClass(ZBX_STYLE_FORM_INPUT_MARGIN),
			(new CNumericBox('operation[esc_step_to]', 0, 5, false, false, false))
				->setAttribute('value', $operation['esc_step_to'] ?? 0)
				->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH),
			(new CDiv())->addClass(ZBX_STYLE_FORM_INPUT_MARGIN), _('(0 - infinitely)')
		]))->setId('operation-step-range')
]);

// Operation steps duration row.
	$form_grid->addItem([
		(new CLabel(_('Step duration'), 'operation_esc_period'))->setId('operation-step-duration-label'),
		(new CFormField([
			(new CTextBox('operation[esc_period]', 0))
				->setAttribute('value', $operation['esc_period'] ?? 0)
				->setWidth(ZBX_TEXTAREA_SMALL_WIDTH)->setId('operation_esc_period'),
			(new CDiv())->addClass(ZBX_STYLE_FORM_INPUT_MARGIN), _('(0 - use action default)')
		]))->setId('operation-step-duration')
	]);
}

// Message recipient is required notice row.
$form_grid->addItem(
	(new CFormField((new CLabel(_('At least one user or user group must be selected.')))
		->setAsteriskMark()
	))->setId('operation-message-notice')
);

$usergroup_table = (new CTable())
	->setId('operation-message-user-groups-table')
	->addStyle('width: 100%;')
	->setHeader([_('User group'), _('Action')]);

$usergroup_table->addItem(
	(new CTag('tfoot', true))
		->addItem(
			(new CCol(
				(new CSimpleButton(_('Add')))
					->addClass(ZBX_STYLE_BTN_LINK)
					->addClass('operation-message-user-groups-footer')
			))->setColSpan(4)
		)->setId('operation-message-user-groups-footer')
);

$form_grid->addItem([
	(new CLabel(_('Send to user groups')))->setId('operation-message-user-groups-label'),
	(new CFormField([
		$usergroup_table,
		(new CScriptTemplate('operation-usergroup-row-tmpl'))->addItem(
			(new CRow([
				new CCol('#{name}'),
				(new CCol([
					(new CButton(null, _('Remove')))
						->addClass(ZBX_STYLE_BTN_LINK)
						->addClass('js-remove'),
					(new CInput('hidden'))
						->setAttribute('value', '#{usrgrpid}')
						->setName('operation[opmessage_grp][][usrgrpid]'),
				]))
			]))
				->setAttribute('data-id','#{usrgrpid}')
		)
	]))
		->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
		->addStyle('min-width: '.ZBX_TEXTAREA_STANDARD_WIDTH.'px;')
		->setId('operation-message-user-groups')
]);

$user_table = (new CTable())
	->setId('operation-message-user-table')
	->addStyle('width: 100%;')
	->setHeader([_('User'), _('Action')]);

$user_table->addItem(
	(new CTag('tfoot', true))
		->addItem(
			(new CCol(
				(new CSimpleButton(_('Add')))
					->addClass(ZBX_STYLE_BTN_LINK)
					->addClass('operation-message-users-footer')
			))->setColSpan(4)
		)->setId('operation-message-users-footer')
);

// Message recipient (users) row.
$form_grid->addItem([
	(new CLabel(_('Send to users')))->setId('operation-message-users-label'),
	(new CFormField([
		$user_table,
		(new CScriptTemplate('operation-user-row-tmpl'))->addItem(
			(new CRow([
				new CCol('#{name}'),
				(new CCol([
					(new CButton(null, _('Remove')))
						->addClass(ZBX_STYLE_BTN_LINK)
						->addClass('js-remove'),
					(new CInput('hidden'))
						->setAttribute('value', '#{id}')
						->setName('operation[opmessage_usr][][userid]'),
				]))
			]))
				->setAttribute('data-id','#{id}')
		)
	]))
		->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
		->addStyle('min-width: '.ZBX_TEXTAREA_STANDARD_WIDTH.'px;')
		->setId('operation-message-users')
]);
array_unshift($data['mediatype_options'], ['name' => '- '._('All').' -', 'mediatypeid' => 0, 'status' => 0]);

$disabled = [];

foreach($data['mediatype_options'] as $mediatype_option) {
	$media[$mediatype_option['mediatypeid']] = $mediatype_option['name'];
	if ($mediatype_option['status'] == MEDIA_TYPE_STATUS_DISABLED) {
		$disabled[] = $mediatype_option['mediatypeid'];
	}
}

$mediatype_options = CSelect::createOptionsFromArray($media);
foreach ($mediatype_options as $option_data) {
	$option = $option_data->toArray();
	if (in_array($option['value'], $disabled)) {
		$option_data->addClass(ZBX_STYLE_RED);
	}
}

// Operation message media type row.
$select_opmessage_mediatype_default = (new CSelect('operation[opmessage][mediatypeid]'))
	->addOptions(CSelect::createOptionsFromArray($media))
	->setFocusableElementId('operation-opmessage-mediatypeid')
	->setValue($operation['opmessage']['mediatypeid'] ?? 0);

$form_grid->addItem([
	(new CLabel(_('Default media type'), $select_opmessage_mediatype_default->getFocusableElementId()))
		->setId('operation-message-mediatype-default-label'),
	(new CFormField($select_opmessage_mediatype_default))
		->setId('operation-message-mediatype-default')
]);

// Operation message media type row (explicit).
$select_opmessage_mediatype = (new CSelect('operation[opmessage][mediatypeid]'))
	->addOptions($mediatype_options)
	->setFocusableElementId('operation-opmessage-mediatypeid')
	->setName('operation[opmessage][mediatypeid]')
	->setValue($operation['opmessage']['mediatypeid'] ?? 0);

$form_grid->addItem([
	(new CLabel(_('Send only to'), $select_opmessage_mediatype->getFocusableElementId()))
		->setId('operation-message-mediatype-only-label'),
	(new CFormField($select_opmessage_mediatype))
		->setId('operation-message-mediatype-only')
		->setName('operation[opmessage][default_msg]')
]);

// Operation custom message checkbox row.
$form_grid->addItem([
	(new CLabel(_('Custom message'), 'operation[opmessage][default_msg]'))->setId('operation-message-custom-label'),
	(new CFormField(
		(new CCheckBox('operation[opmessage][default_msg]', $operation['opmessage']['default_msg']))
			->setId('operation_opmessage_default_msg')
			->setChecked($operation['opmessage']['default_msg'] != '1')
	))->setId('operation-message-custom')
]);

// Operation custom message subject row.
$form_grid->addItem([
	(new CLabel(_('Subject')))->setId('operation-message-subject-label'),
	(new CTextBox('operation[opmessage][subject]'))
		->setAttribute('value', $operation['opmessage']['default_msg'] == 1 ? '' : $operation['opmessage']['subject'])
		->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
		->setId('operation-message-subject')
]);

// Operation custom message body row.
$form_grid->addItem([
	(new CLabel(_('Message')))->setId('operation-message-label'),
	(new CTextArea('operation[opmessage][message]'))
		->setValue($operation['opmessage']['default_msg'] == 1 ? '' : $operation['opmessage']['message'])
		->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
		->setId('operation-message-body')
]);

$opcommand_hst_value = null;

if (array_key_exists('0', $operation['opcommand_hst'])) {
	if (array_key_exists('hostid', $operation['opcommand_hst'][0])) {
		$opcommand_hst_value = $operation['opcommand_hst'][0]['hostid'];
	}
}

$multiselect_values_host = [];
$multiselect_values_host_grp = [];
$hosts = [];

if ($operation['opcommand_hst']) {
	foreach($operation['opcommand_hst'] as $host) {
		if (array_key_exists('0', $host)) {
			foreach ($host as $h) {
				$hosts['id'] = $h['hostid'];
				$hosts['name'] = $h['name'];
				$multiselect_values_host[] = $hosts;
			}
		}
	}
}

if ($operation['opcommand_grp']) {
	foreach ($operation['opcommand_grp'] as $group) {
		$host_group['id'] = $group['groupid'];
		$host_group['name'] = $group['name'];
		$multiselect_values_host_grp[] = $host_group;
	}
}

if (array_key_exists('opcommand_hst', $operation) && array_key_exists('opcommand_grp', $operation)) {
	// Command execution targets row.
	$form_grid->addItem([
		(new CLabel(_('Target list')))
			->setId('operation-command-targets-label')
			->setAsteriskMark(),
		(new CFormField(
			(new CFormGrid())
				->cleanItems()
				->addItem([
					new CLabel(_('Current host')),
					(new CFormField((new CCheckBox('operation[opcommand_hst][][hostid][current_host]', '0'))
						->setChecked($opcommand_hst_value === 0)
					))->setId('operation-command-checkbox')
				])
				->addItem([
					(new CLabel(_('Host'))),
					(new CMultiSelect([
						'name' => 'operation[opcommand_hst][][hostid]',
						'object_name' => 'hosts',
						'data' => $multiselect_values_host,
						'popup' => [
							'parameters' => [
								'multiselect' => '1',
								'srctbl' => 'hosts',
								'srcfld1' => 'hostid',
								'dstfrm' => 'action.edit',
								'dstfld1' => 'operation_opcommand_hst__hostid',
								'editable' => '1',
								'disableids' => array_column($multiselect_values_host, 'id')
							]
						]
					]))->setWidth(ZBX_TEXTAREA_MEDIUM_WIDTH)
				])
				->addItem([
					new CLabel(_('Host group')),
					(new CMultiSelect([
						'name' => 'operation[opcommand_grp][][groupid]',
						'object_name' => 'hostGroup',
						'data' => $multiselect_values_host_grp,
						'popup' => [
							'parameters' => [
								'multiselect' => '1',
								'srctbl' => 'host_groups',
								'srcfld1' => 'groupid',
								'dstfrm' => 'action.edit',
								'dstfld1' => 'operation_opcommand_grp__groupid',
								'editable' => '1',
								'disableids' => array_column($multiselect_values_host_grp, 'id')
							]
						]
					]))->setWidth(ZBX_TEXTAREA_MEDIUM_WIDTH)
				])
		))
			->setId('operation-command-targets')
			->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
			->addStyle('min-width: '.ZBX_TEXTAREA_STANDARD_WIDTH.'px;')
	]);
}

$multiselect_values_ophost_grp = [];
$multiselect_values_optemplate = [];

foreach ($operation['opgroup'] as $group) {
	$host_group['id'] = $group['groupid'];
	$host_group['name'] = $group['name'];
	$multiselect_values_ophost_grp[] = $host_group;
}

// Add / remove host group attribute row.
$form_grid->addItem([
	(new CLabel(_('Host groups'),'operation-attr-hostgroups'))
		->setId('operation-attr-hostgroups-label')
		->setAsteriskMark(),
	(new CFormField(
		(new CMultiSelect([
			'name' => 'operation[opgroup][][groupid]',
			'object_name' => 'hostGroup',
			'data' => $multiselect_values_ophost_grp,
			'popup' => [
				'parameters' => [
					'multiselect' => '1',
					'srctbl' => 'host_groups',
					'srcfld1' => 'groupid',
					'dstfrm' => 'action.edit',
					'dstfld1' => 'operation_opgroup__groupid',
					'editable' => '1',
					'disableids' => array_column($multiselect_values_ophost_grp, 'id')
				]
			]
		]))
			->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
			->setAriaRequired()
			->setWidth(ZBX_TEXTAREA_MEDIUM_WIDTH))
	)->setId('operation-attr-hostgroups')
]);

foreach ($operation['optemplate'] as $template) {
	$templates['id'] = $template['templateid'];
	$templates['name'] = $template['name'];
	$multiselect_values_optemplate[] = $templates;
}

// Link / unlink templates attribute row.
$form_grid->addItem([
	(new CLabel(_('Templates')))
		->setId('operation-attr-templates-label')
		->setAsteriskMark(),
	(new CFormField(
		(new CMultiSelect([
			'name' => 'operation[optemplate][][templateid]',
			'object_name' => 'templates',
			'data' => $multiselect_values_optemplate,
			'popup' => [
				'parameters' => [
					'multiselect' => '1',
					'srctbl' => 'templates',
					'srcfld1' => 'hostid',
					'dstfrm' => 'action.edit',
					'dstfld1' => 'operation_optemplate__templateid',
					'editable' => '1',
					'disableids' => array_column($multiselect_values_optemplate, 'id')
				]
			]
		]))->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
			->setAriaRequired()
			->setWidth(ZBX_TEXTAREA_MEDIUM_WIDTH))
	)->setId('operation-attr-templates')
]);

// Host inventory mode attribute row.
$form_grid->addItem([
	(new CLabel(_('Inventory mode'), 'operation_opinventory_inventory_mode'))->setId('operation-attr-inventory-label'),
	(new CRadioButtonList('operation[opinventory][inventory_mode]', HOST_INVENTORY_MANUAL))
		->addValue(_('Manual'), HOST_INVENTORY_MANUAL)
		->addValue(_('Automatic'), HOST_INVENTORY_AUTOMATIC)
		->setModern(true)
		->addClass('form-field')
		->setId('operation-attr-inventory')
]);

// Conditions type of calculation row.
$select_operation_evaltype = (new CSelect('operation[evaltype]'))
	->setValue($data['operation']['evaltype'])
	->setId('operation-evaltype')
	->setFocusableElementId('operation-evaltype')
	->addOption(new CSelectOption(CONDITION_EVAL_TYPE_AND_OR, _('And/Or')))
	->addOption(new CSelectOption(CONDITION_EVAL_TYPE_AND, _('And')))
	->addOption(new CSelectOption(CONDITION_EVAL_TYPE_OR, _('Or')));

$form_grid->addItem([
	(new CLabel(_('Type of calculation'), $select_operation_evaltype->getFocusableElementId()))
		->setId('operation-evaltype-label'),
	(new CFormField([
		$select_operation_evaltype,
		(new CDiv())->addClass(ZBX_STYLE_FORM_INPUT_MARGIN),
		(new CSpan())
			->setId('operation-condition-evaltype-formula'),
	]))->setId('operation-condition-row')
]);

$conditions_table = (new CTable())
	->setId('operation-condition-list')
	->addStyle('width: 100%;')
	->setHeader([_('Label'), _('Name'), _('Action')]);

$conditions_table->addItem(
	(new CTag('tfoot', true))
		->addItem(
			(new CCol(
				(new CSimpleButton(_('Add')))
					->setAttribute('data-eventsource', $data['eventsource'])
					->addClass(ZBX_STYLE_BTN_LINK)
					->addClass('operation-condition-list-footer')
			))->setColSpan(4)
		)
);

// Conditions row.
if ($data['eventsource'] == EVENT_SOURCE_TRIGGERS && $data['recovery'] == ACTION_OPERATION) {
	$form_grid->addItem([
		(new CLabel(_('Conditions')))->setId('operation-condition-list-label'),
		(new CFormField([
			$conditions_table,
			(new CScriptTemplate('operation-condition-row-tmpl'))->addItem(
				(new CRow([
					(new CCol('#{label}'))
						->setAttribute('data-conditiontype', '#{conditiontype}')
						->setAttribute('data-formulaid', '#{label}')
						->addClass('label'),
					new CCol('#{name}'),
					(new CCol([
						(new CButton(null, _('Remove')))
							->addClass(ZBX_STYLE_BTN_LINK)
							->addClass('js-remove'),
						(new CInput('hidden'))
							->setAttribute('value', '#{conditiontype}')
							->setName('operation[opconditions][#{row_index}][conditiontype]'),
						(new CInput('hidden'))
							->setAttribute('value', '#{operator}')
							->setName('operation[opconditions][#{row_index}][operator]'),
						(new CInput('hidden'))
							->setAttribute('value', '#{value}')
							->setName('operation[opconditions][#{row_index}][value]')
					])
					)
				]))
					->setAttribute('data-id','#{row_index}')
					->addClass('form_row')
			)
		]))
			->setId('operation-condition-table')
			->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
			->addStyle('min-width: '.ZBX_TEXTAREA_STANDARD_WIDTH.'px;')
	]);
}

$form->addItem($form_grid);

$buttons = [
	[
		'title' => array_key_exists('details',$data['operation']) ? _('Update') : _('Add'),
		'class' => 'js-add',
		'keepOpen' => true,
		'isSubmit' => true,
		'action' => 'operation_popup.submit();'
	]
];

$output = [
	'header' => _('Operation details'),
	'body' => $form->toString(),
	'buttons' => $buttons,
	'script_inline' => getPagePostJs().$this->readJsFile('popup.operation.edit.js.php').
		'operation_popup.init('.json_encode([
			'eventsource' => $data['eventsource'],
			'recovery_phase' => $data['recovery'],
			'data' => $operation,
			'actionid' => $data['actionid']
		]).');'
];

echo json_encode($output);
