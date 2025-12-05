<?php
/**
 * @file
 * Cost-quantity component's template
 */
?>

<div :style="additionalCss" class="calc-item ccb-field ccb-field-quantity ccb-fields-tooltip" :class="{required: requiredWrapperActive, [quantityField.additionalStyles]: quantityField.additionalStyles}" :data-id="quantityField.alias" :data-repeater="repeater">
	<div class="calc-item__title">
		<span> {{ quantityField.label }} </span>
		<span class="ccb-required-mark" v-if="quantityField.required">*</span>
	</div>

	<div class="calc-item__description before">
		<span v-text="quantityField.description"></span>
	</div>
	<div v-if="showMinMaxQuantity" class="calc-item__description before">
		<span v-if="hasMinValue">
			<?php esc_html_e( 'Min', 'cost-calculator-builder' ); ?>: {{ minText }}
		</span>
		<span v-if="hasMinMaxValues"> - </span>
		<span v-if="hasMaxValue">
			<?php esc_html_e( 'Max', 'cost-calculator-builder' ); ?>: {{ maxText }}
		</span>
	</div>
	<div :class="['calc-input-wrapper ccb-field', 'calc_' + quantityField.alias]">
		<input @focusout="parseField" @keypress="intValueFilter($event)" name="quantityField" type="text" v-model="quantityValue" @focus="$event.target.select()" :placeholder="quantityField.placeholder" class="calc-input number ccb-field ccb-appearance-field">
		<span @click="increment" class="input-number-counter up">
			<i class="ccb-icon-Path-3486"></i>
		</span>
		<span @click="decrement" class="input-number-counter down">
			<i class="ccb-icon-Path-3485"></i>
		</span>
	</div>
	<div v-if="showMinMaxQuantity" class="calc-item__description after">
		<span v-if="hasMinValue">
			<?php esc_html_e( 'Min', 'cost-calculator-builder' ); ?>: {{ minText }}
		</span>
		<span v-if="hasMinMaxValues"> - </span>
		<span v-if="hasMaxValue">
			<?php esc_html_e( 'Max', 'cost-calculator-builder' ); ?>: {{ maxText }}
		</span>
	</div>

	<div class="calc-item__description after">
		<span v-text="quantityField.description"></span>
	</div>
</div>
