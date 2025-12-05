<template>
  <div class="ccb-promocodes">
    <div class="ccb-promocodes__labels">
      <div class="ccb-promocodes__labels--info">
        {{ translationsStore.getTranslations.havePromoCode }}
      </div>

      <div v-if="errorType !== ''" class="ccb-required-tooltip">
        <template v-if="errorType === 'already_exist'">
          {{ translationsStore.getTranslations.promoCodeIsApplied }}
        </template>
        <template v-else-if="errorType === 'promocode_not_exist'">
          {{ translationsStore.getTranslations.promoCodeNotExist }}
        </template>
      </div>

      <div class="ccb-promocodes__labels--toggle">
        {{ translationsStore.getTranslations.promoCode }}
        <span @click="toggle">
          {{
            togglePromocode
              ? translationsStore.getTranslations.hide
              : translationsStore.getTranslations.show
          }}
        </span>
      </div>
    </div>
    <div v-if="togglePromocode" class="ccb-promocodes__input">
      <div class="ccb-promocodes__input--wrapper">
        <input v-model="promocode" type="text" @input="errorType = ''" />
      </div>
      <div>
        <Button
          type="success"
          :on-click="applyPromocodeHandler"
          :text="translationsStore.getTranslations.apply"
        />
      </div>
    </div>

    <div class="ccb-promocodes__list" v-if="togglePromocode">
      <div class="ccb-promocodes__list-title" v-if="promocodesList.length">
        {{ translationsStore.getTranslations.appliedPromoCodes }}:
      </div>
      <div
        v-for="(p, idx) in promocodesList"
        :key="p"
        class="ccb-promocodes__list--item"
      >
        <span>{{ p }}</span>
        <span class="action" @click="() => removePromocodeHandler(idx)">
          <i class="ccb-icon-close"></i>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import Button from "@/widget/shared/ui/components/Button";
import { useDiscounts } from "@/widget/actions/discounts/composable/useDiscounts.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

const { promocodes, applyPromocode, removePromocode, originalPromocodes } =
  useDiscounts();
const promocode = ref<string>("");
const errorType = ref<string>("");
const togglePromocode = ref<boolean>(true);

const translationsStore = useTranslationsStore();

const toggle = () => {
  togglePromocode.value = !togglePromocode.value;
};

const promocodesList = computed(() => {
  return promocodes.value;
});

const applyPromocodeHandler = (): void => {
  if (promocode.value.length) {
    if (!originalPromocodes.value.includes(promocode.value)) {
      errorType.value = "promocode_not_exist";
    } else if (!promocodesList.value.includes(promocode.value)) {
      applyPromocode(promocode.value);
      promocode.value = "";
    } else if (promocodesList.value.includes(promocode.value)) {
      errorType.value = "already_exist";
    }
  }
};

const removePromocodeHandler = (idx: number): void => {
  removePromocode(idx);
  errorType.value = "";
};
</script>

<style scoped lang="scss">
.ccb-promocodes {
  padding: 0 0 10px;

  &__list {
    display: flex;
    gap: 5px;
    padding: 10px 0;
    flex-wrap: wrap;
    align-items: center;

    &-title {
      font-size: 12px;
      font-weight: 500;
      word-break: break-word;
      color: var(--ccb-text-color);
      opacity: 0.6;
    }

    &--item {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 5px 10px 6px;
      border-radius: 5px;
      background-color: var(--ccb-container-dark-color);
      color: var(--ccb-text-color);
      font-size: 12px;
      font-weight: 500;
      line-height: 1.3;
      word-break: break-all;
      opacity: 0.6;

      .action {
        cursor: pointer;
        display: inline-block;
        margin-left: 10px;
        font-size: 11px;
        opacity: 0.7;
        transition: ease-in-out 200ms;
        line-height: 0.3;
      }
    }
  }

  &__labels {
    display: flex;
    flex-direction: column;
    row-gap: 10px;

    &--info {
      color: var(--ccb-accent-color);
      font-size: 12px;
      font-style: normal;
      font-weight: 700;
      line-height: normal;
    }

    &--toggle {
      font-size: 14px;
      font-weight: 600;
      color: #001931;
      margin-bottom: 4px;
      word-break: break-word;

      span {
        cursor: pointer;
        color: var(--ccb-accent-color);
        margin-left: 5px;
      }
    }
  }

  &__input {
    width: 100%;
    display: flex;
    flex-wrap: nowrap;
    column-gap: 10px;
    align-items: stretch;

    &--wrapper {
      flex: 1;
    }

    input {
      border: var(--ccb-fields-border) var(--ccb-fields-border-style)
        var(--ccb-fields-border-color);
      border-radius: var(--ccb-fields-border-radius);
      padding: 12px var(--ccb-field-side-indent);
      min-height: var(--ccb-field-button-height);
      background-color: var(--ccb-fields-bg-color);
      font-size: var(--ccb-fields-button-size);
      font-weight: var(--ccb-field-weight);
      box-sizing: border-box;
      width: 100%;
      outline: none;
      color: var(--ccb-text-color);

      @media only screen and (max-width: 480px) {
        padding: 12px var(--ccb-mobile-field-side-indent);
        min-height: var(--ccb-mobile-field-button-height);
        font-weight: var(--ccb-mobile-field-weight);
        font-size: var(--ccb-mobile-fields-button-size);
      }

      &:focus {
        border-color: var(--ccb-accent-color);
      }
    }

    button {
      padding: 10px 20px;
    }
  }
}

.ccb-required-tooltip {
  background-color: var(--ccb-error-color);
  font-size: 14px;
  font-weight: 500;
  color: #fff;
  width: fit-content;
  border-radius: 4px;
  padding: 5px 10px;
  line-height: 1;
  position: absolute;

  &:after {
    content: "";
    width: 0;
    height: 0;
    display: block;
    position: absolute;
    z-index: 10;
    border: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 10px solid var(--ccb-error-color);
    left: 5px;
    margin-right: 15px;
  }
}
</style>
