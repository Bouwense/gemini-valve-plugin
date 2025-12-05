<?php
/**
 * @file
 * Cost-text component's template
 */
?>

<div :style="additionalCss" class="calc-item ccb-field ccb-fields-tooltip" :class="{required: requiredActive, [textField.additionalStyles]: textField.additionalStyles}" :data-id="textField.alias" :data-repeater="repeater">
	<div class="calc-item__title">
		<span>{{ textField.label }}</span>
		<span class="ccb-required-mark" v-if="textField.required">*</span>
	</div>

	<div class="calc-item__description before">
		<span v-text="textField.description"></span>
	</div>
	<div class="calc-textarea-box">
		<textarea v-model="textareaValue" @change="onChange" :id="labelId" :placeholder="textField.placeholder" :class="['calc-textarea ccb-appearance-field calc-item']"></textarea>
	</div>

	<div class="calc-item__description after">
		<span v-text="textField.description"></span>
	</div>
</div>
