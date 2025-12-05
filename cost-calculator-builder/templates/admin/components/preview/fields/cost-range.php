<?php
/**
 * @file
 * Cost-quantity component's template
 */

	$lang = get_bloginfo( 'language' );
?>
<div :style="additionalCss" class="calc-item ccb-field" :class="{rtl: rtlClass('<?php echo esc_attr( $lang ); ?>'), required: requiredActive, [rangeField.additionalStyles]: rangeField.additionalStyles}" :data-id="rangeField.alias" :data-repeater="repeater">
	<div class="calc-range" :class="['calc_' + rangeField.alias]">
		<div class="calc-item__title ccb-range-field">
			<div class="ccb-range-label">
				{{ rangeField.label }}
				<span class="ccb-required-mark" v-if="rangeField.required">*</span>
			</div>
			<div class="ccb-range-value"> {{ getFormatedValue }}</div>
		</div>

		<div class="calc-item__description before">
			<span v-text="rangeField.description"></span>
		</div>

		<div :class="['range_' + rangeField.alias]" class="calc-range-slider" :style="getStyles">
			<input type="range" :min="min" :max="max" :step="step" v-model="rangeValue" @input="change">
			<output class="cost-calc-range-output-free"></output>
			<div class='calc-range-slider__progress'></div>
		</div>

		<div class="calc-range-slider-min-max">
			<span>{{ minText }}</span>
			<span>{{ maxText }}</span>
		</div>

		<div class="calc-item__description after" >
			<span v-text="rangeField.description"></span>
		</div>
	</div>
</div>
