<div class="cbb-edit-field-container">
	<div class="ccb-edit-field-header">
		<span class="ccb-edit-field-title ccb-heading-3 ccb-bold"><?php esc_html_e( 'Quantity field', 'cost-calculator-builder' ); ?></span>
		<div class="ccb-field-actions">
			<button class="ccb-button default" @click="$emit( 'cancel' )"><?php esc_html_e( 'Cancel', 'cost-calculator-builder' ); ?></button>
			<button class="ccb-button success" @click.prevent="save(quantityField, id, index, quantityField.alias)"><?php esc_html_e( 'Save', 'cost-calculator-builder' ); ?></button>
		</div>
	</div>
	<div class="ccb-grid-box ccb-vertical">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="ccb-edit-field-switch">
						<div class="ccb-edit-field-switch-item ccb-default-title" :class="{active: tab === 'main'}" @click="tab = 'main'">
							<?php esc_html_e( 'Element', 'cost-calculator-builder' ); ?>
							<span class="ccb-fields-required" v-if="errorsCount > 0">{{ errorsCount }}</span>
						</div>
						<div class="ccb-edit-field-switch-item ccb-default-title" :class="{active: tab === 'style'}" @click="tab = 'style'">
							<?php esc_html_e( 'Styles', 'cost-calculator-builder' ); ?>
						</div>
						<div class="ccb-edit-field-switch-item ccb-default-title" :class="{active: tab === 'settings'}" @click="tab = 'settings'">
							<?php esc_html_e( 'Settings', 'cost-calculator-builder' ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="container" v-show="tab === 'main'">
			<div class="row ccb-p-t-15">
				<div class="col">
					<div class="ccb-input-wrapper">
						<span class="ccb-input-label"><?php esc_html_e( 'Name', 'cost-calculator-builder' ); ?></span>
						<input type="text" class="ccb-heading-5 ccb-light" placeholder="<?php esc_attr_e( 'Enter field name', 'cost-calculator-builder' ); ?>" v-model.trim="quantityField.label">
					</div>
				</div>
				<div class="col">
					<div class="ccb-input-wrapper">
						<span class="ccb-input-label"><?php esc_html_e( 'Placeholder', 'cost-calculator-builder' ); ?></span>
						<input type="text" class="ccb-heading-5 ccb-light" placeholder="<?php esc_attr_e( 'Enter field placeholder', 'cost-calculator-builder' ); ?>" v-model.trim="quantityField.placeholder">
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-15">
				<div class="col-12">
					<div class="ccb-input-wrapper">
						<span class="ccb-input-label"><?php esc_html_e( 'Description', 'cost-calculator-builder' ); ?></span>
						<input type="text" class="ccb-heading-5 ccb-light" placeholder="<?php esc_attr_e( 'Enter field description', 'cost-calculator-builder' ); ?>" v-model.trim="quantityField.description">
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-15">
				<div class="col-6">
					<div class="ccb-input-wrapper number">
						<span class="ccb-input-label"><?php esc_html_e( 'Step', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-input-box">
							<input type="text" class="ccb-heading-5 ccb-light" :class="{'ccb-input-required': isObjectHasPath(errors, ['step'] ) && errors.step}" name="step" min="0" step="1" @input="errors.step=false" v-model="quantityField.step" placeholder="<?php esc_attr_e( 'Enter field step', 'cost-calculator-builder' ); ?>">
							<span @click="numberCounterAction('step')" class="input-number-counter up"></span>
							<span @click="numberCounterAction('step', '-')" class="input-number-counter down"></span>
						</div>
						<span class="ccb-error-tip default" v-if="isObjectHasPath(errors, ['step'] ) && errors.step" v-html="errors.step"></span>
					</div>
				</div>
				<div class="col-6">
					<div class="ccb-input-wrapper number">
						<span class="ccb-input-label"><?php esc_html_e( 'Default Value', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-input-box">
							<input type="text" class="ccb-heading-5 ccb-light" :class="{'ccb-input-required': isObjectHasPath(errors, ['default'] ) && errors.default}" name="default" min="0" step="1" v-model="quantityField.default" placeholder="<?php esc_attr_e( 'Enter field default value', 'cost-calculator-builder' ); ?>">
							<span @click="numberCounterAction('default')" class="input-number-counter up"></span>
							<span @click="numberCounterAction('default', '-')" class="input-number-counter down"></span>
						</div>
						<span class="ccb-error-tip default" v-if="isObjectHasPath(errors, ['default'] ) && errors.default" v-html="errors.default"></span>
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-15">
				<div class="col-6">
					<div class="ccb-input-wrapper number">
						<span class="ccb-input-label"><?php esc_html_e( 'Min Value', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-input-box">
							<input type="text" class="ccb-heading-5 ccb-light" :class="{'ccb-input-required': isObjectHasPath(errors, ['min'] ) && errors.min}" name="min" min="0" step="1" v-model="quantityField.min" @input="errors.min=false" placeholder="<?php esc_attr_e( 'Enter field minimum value', 'cost-calculator-builder' ); ?>">
							<span @click="numberCounterAction('min')" class="input-number-counter up"></span>
							<span @click="numberCounterAction('min', '-')" class="input-number-counter down"></span>
						</div>
						<span class="ccb-error-tip default" v-if="isObjectHasPath(errors, ['min'] ) && errors.min" v-html="errors.min"></span>
					</div>
				</div>
				<div class="col-6">
					<div class="ccb-input-wrapper number">
						<span class="ccb-input-label"><?php esc_html_e( 'Max Value', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-input-box">
							<input type="text" class="ccb-heading-5 ccb-light" :class="{'ccb-input-required': isObjectHasPath(errors, ['max'] ) && errors.max}" name="max" min="0" step="1" @input="errors.max=false" v-model="quantityField.max" placeholder="<?php esc_attr_e( 'Enter field maximum value', 'cost-calculator-builder' ); ?>">
							<span @click="numberCounterAction('max')" class="input-number-counter up"></span>
							<span @click="numberCounterAction('max', '-')" class="input-number-counter down"></span>
						</div>
						<span class="ccb-error-tip default" v-if="isObjectHasPath(errors, ['max'] ) && errors.max" v-html="errors.max"></span>
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-15">
				<div class="col-6">
					<div class="ccb-input-wrapper" :class="{ 'disabled': !quantityField.multiply && quantityField.allowCurrency || quantityField.fieldCurrency }">
						<span class="ccb-input-label"><?php esc_html_e( 'Unit Name', 'cost-calculator-builder' ); ?></span>
						<input type="text" maxlength="18" class="ccb-heading-5 ccb-light" v-model.trim="quantityField.sign" placeholder="<?php esc_attr_e( 'Enter unit symbol', 'cost-calculator-builder' ); ?>">
					</div>
				</div>
				<div class="col-6">
					<div class="ccb-disable-msg" v-if="!quantityField.multiply && quantityField.allowCurrency">
						<span><?php esc_html_e( 'Currency sign is ON', 'cost-calculator-builder' ); ?></span>
					</div>
					<div class="ccb-select-box" style="padding-top: 27px;" v-else>
						<div class="ccb-select-wrapper">
							<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
							<select class="ccb-select" v-model="quantityField.unitPosition">
								<option value="right" selected><?php esc_html_e( 'On the right', 'cost-calculator-builder' ); ?></option>
								<option value="left"><?php esc_html_e( 'On the left', 'cost-calculator-builder' ); ?></option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-15">
				<div class="col-6">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="quantityField.multiply"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Multiply by Unit Price', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
				<div class="col-6">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="quantityField.hideMinMax"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Hide Min/Max Values', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-15" v-if="quantityField.multiply">
				<div class="col-12">
					<div class="list-header">
						<div class="ccb-multiply">
							<span class="ccb-multiply__bg">=</span>
							<span class="ccb-multiply__bg"><?php esc_html_e( 'Selected value', 'cost-calculator-builder' ); ?></span>
							<span class="ccb-multiply__icon"><i class="ccb-icon-close"></i></span>
							<div class="ccb-input-wrapper number">
								<div class="ccb-input-box">
									<input type="text" class="ccb-heading-5 ccb-light" name="unit" min="0" step="1" :class="{'ccb-input-required': isObjectHasPath(errors, ['unit'] ) && errors.unit}" v-model="quantityField.unit" placeholder="<?php esc_attr_e( 'Enter unit', 'cost-calculator-builder' ); ?>">
									<span @click="numberCounterAction('unit')" class="input-number-counter up"></span>
									<span @click="numberCounterAction('unit', '-')" class="input-number-counter down"></span>
								</div>
								<span class="ccb-error-tip default" v-if="isObjectHasPath(errors, ['unit'] ) && errors.unit" v-html="errors.unit"></span>
							</div>
							<div class="ccb-input-wrapper" style="margin-left: 10px; width: 160px;" :class="{'disabled': quantityField.fieldCurrency || quantityField.allowCurrency }">
								<input type="text" maxlength="18" class="ccb-heading-5 ccb-light" v-model.trim="quantityField.unitSymbol" placeholder="<?php esc_attr_e( 'Unit (kg, cm,...)', 'cost-calculator-builder' ); ?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="container" v-show="tab === 'style'">
			<?php if ( defined( 'CCB_PRO' ) ) : ?>
				<div class="row ccb-p-t-15" style="align-items: flex-end !important;" v-if="quantityField.styles">
					<div class="col-6">
						<div class="ccb-select-box">
							<span class="ccb-select-label"><?php esc_html_e( 'Style', 'cost-calculator-builder' ); ?></span>
							<div class="ccb-select-wrapper">
								<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
								<select class="ccb-select" v-model="quantityField.styles.style" style="padding-right: 30px !important;">
									<option v-for="opt in getQuantityStyles" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15">
					<div class="col-12">
						<div class="ccb-style-preview">
							<span class="ccb-style-preview-header"><?php esc_html_e( 'Style preview', 'cost-calculator-builder' ); ?></span>
							<img :src="getCurrentImage">
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15" v-if="quantityField.styles && quantityField.styles.style !== 'default'">
				<div class="col-6">
					<div class="ccb-select-box">
							<span class="ccb-select-label"><?php esc_html_e( 'Buttons position', 'cost-calculator-builder' ); ?></span>
							<div class="ccb-select-wrapper">
								<i class="ccb-icon-Path-3485 ccb-select-arrow"></i> 
								<select class="ccb-select big" v-model="quantityField.buttonsPosition">
									<option value="right" selected="selected"><?php esc_html_e( 'Right Side', 'cost-calculator-builder' ); ?></option>
									<option value="both"><?php esc_html_e( 'Both Sides', 'cost-calculator-builder' ); ?></option>
								</select>
							</div>
						</div>
					</div>
					<div class="col-6" style="padding-top: 27px;">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="quantityField.separation">
								<label></label>
							</div> 
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Separation', 'cost-calculator-builder' ); ?></h6>
						</div>
					</div>
				</div>
			<?php else : ?>
				<div class="row ccb-p-t-15">
					<div class="calc-styles-pro-container">
						<div class="calc-styles-pro-header">
							<div class="calc-styles-pro-header-left">
								<span class="calc-styles-pro-icon-box">
									<i class="ccb-icon-Lock-filled"></i>
								</span>
								<span class="calc-styles-pro-header-box">
									<span class="ccb-heading-4 ccb-bold"><?php esc_html_e( 'Unlock PRO element styles', 'cost-calculator-builder' ); ?></span>
								</span>
							</div>
							<div class="calc-styles-pro-header-right">
								<a href="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcwpadmin&utm_medium=freetoprobutton&utm_campaign=checkbox_styles&licenses=1&billing_cycle=annual" target="_blank">
									<?php esc_html_e( 'Get Pro', 'cost-calculator-builder' ); ?>
								</a>
							</div>
						</div>
						<div class="calc-styles-pro-content">
							<img src="<?php echo esc_attr( CALC_URL . '/frontend/dist/img/styles/checkbox/checkbox-pro.gif' ); ?>">
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<div class="container" v-show="tab === 'settings'">
			<div class="row ccb-p-t-15">
				<div class="col-6">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="quantityField.allowCurrency"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Currency Sign', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
				<div class="col-6">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="quantityField.enabled_currency_settings"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Currency Settings', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
				<div class="col-6 ccb-p-t-10">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="quantityField.allowRound"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Round Value', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
				<?php if ( ccb_pro_active() ) : ?>
					<div class="col-6 ccb-p-t-10">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="quantityField.required"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Required', 'cost-calculator-builder' ); ?></h6>
						</div>
					</div>
				<?php endif; ?>
				<div class="col-6 ccb-p-t-10" v-if="!disableFieldHiddenByDefault(quantityField)">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="quantityField.hidden"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Hidden by Default', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
				<div class="col-6 ccb-p-t-10" v-if="!disableFieldHiddenByDefault(quantityField)">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="quantityField.calculateHidden"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Calculate Hidden by Default', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
				<div class="col-6 ccb-p-t-10">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="quantityField.addToSummary"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Show in Grand Total', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
				<div class="col-6 ccb-p-t-10">
					<div class="list-header">
						<div class="ccb-switch">
							<input type="checkbox" v-model="quantityField.fieldCurrency"/>
							<label></label>
						</div>
						<h6 class="ccb-heading-5"><?php esc_html_e( 'Set Measurement Unit', 'cost-calculator-builder' ); ?></h6>
					</div>
				</div>
			</div>
			<div class="row row-currency" :class="{'disabled': !quantityField.fieldCurrency}">
				<div class="col-4">
					<div class="ccb-input-wrapper">
						<span class="ccb-input-label"><?php esc_html_e( 'Unit Symbol', 'cost-calculator-builder' ); ?></span>
						<input type="text" maxlength="18" v-model="fieldCurrency.currency" placeholder="<?php esc_attr_e( 'Enter unit symbol', 'cost-calculator-builder' ); ?>">
					</div>
				</div>
				<div class="col-4">
					<div class="ccb-select-box">
						<span class="ccb-select-label"><?php esc_html_e( 'Position', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-select-wrapper">
							<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
							<select class="ccb-select" v-model="fieldCurrency.currencyPosition">
								<option value="left"><?php esc_html_e( 'Left', 'cost-calculator-builder' ); ?></option>
								<option value="right"><?php esc_html_e( 'Right', 'cost-calculator-builder' ); ?></option>
								<option value="left_with_space"><?php esc_html_e( 'Left with space', 'cost-calculator-builder' ); ?></option>
								<option value="right_with_space"><?php esc_html_e( 'Right with space', 'cost-calculator-builder' ); ?></option>
							</select>
						</div>
					</div>
				</div>
				<div class="col-4">
					<div class="ccb-select-box">
						<span class="ccb-select-label"><?php esc_html_e( 'Thousands Separator', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-select-wrapper">
							<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
							<select class="ccb-select" v-model="fieldCurrency.thousands_separator">
								<option value=","><?php esc_html_e( ' Comma ', 'cost-calculator-builder' ); ?></option>
								<option value="."><?php esc_html_e( ' Dot ', 'cost-calculator-builder' ); ?></option>
								<option value="'"><?php esc_html_e( ' Apostrophe ', 'cost-calculator-builder' ); ?></option>
								<option value=" "><?php esc_html_e( ' Space ', 'cost-calculator-builder' ); ?></option>
							</select>
						</div>
					</div>
				</div>
				<div class="col-4">
					<div class="ccb-input-wrapper number">
						<span class="ccb-input-label"><?php esc_html_e( 'Number of Decimals', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-input-box">
							<input type="number" name="option_num_after_integer" v-model="fieldCurrency.num_after_integer" min="1" max="8" placeholder="<?php esc_attr_e( 'Enter decimals', 'cost-calculator-builder' ); ?>">
							<span class="input-number-counter up" @click="numberCounterAction('num_after_integer')"></span>
							<span class="input-number-counter down" @click="numberCounterAction('num_after_integer', '-')"></span>
						</div>
					</div>
				</div>
				<div class="col-4">
					<div class="ccb-select-box">
						<span class="ccb-select-label"><?php esc_html_e( 'Decimal Separator', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-select-wrapper">
							<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
							<select class="ccb-select" v-model="fieldCurrency.decimal_separator">
								<option value=","><?php esc_html_e( ' Comma ', 'cost-calculator-builder' ); ?></option>
								<option value="."><?php esc_html_e( ' Dot ', 'cost-calculator-builder' ); ?></option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="row ccb-p-t-15">
				<div class="col-12">
					<div class="ccb-input-wrapper">
						<span class="ccb-input-label"><?php esc_html_e( 'Additional Classes', 'cost-calculator-builder' ); ?></span>
						<textarea class="ccb-heading-5 ccb-light" v-model="quantityField.additionalStyles" placeholder="<?php esc_attr_e( 'Set Additional Classes', 'cost-calculator-builder' ); ?>"></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
