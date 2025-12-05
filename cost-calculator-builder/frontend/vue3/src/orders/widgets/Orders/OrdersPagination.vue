<template>
  <div class="pagination">
    <div class="pagination-controls">
      <button
        class="page-btn"
        :class="{ disabled: currentPage === 1 }"
        @click="prevPage"
      >
        <i class="ccb-icon-Left"></i>
      </button>
      <template v-for="page in pages" :key="page">
        <template v-if="page === currentPage">
          <span class="current-page">{{ page }}</span>
        </template>
        <template v-else>
          <button
            class="page-btn"
            :class="{ disabled: page === '...', 'ccb-dots': page === '...' }"
            @click="setPage(page as number)"
          >
            {{ page }}
          </button>
        </template>
      </template>
      <button
        class="page-btn"
        :class="{ disabled: currentPage === totalPages }"
        @click="nextPage"
      >
        <i class="ccb-icon-Left" style="transform: rotate(180deg)"></i>
      </button>
    </div>
    <div class="pagination-border"></div>
    <div class="pagination-per-page">
      <select v-model="pageSize" @change="handlePageSizeChange">
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </select>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useOrdersStore } from "@/orders/app/providers/stores/orders";
import { debounce } from "@/orders/shared/utils/useDebounce";

const ordersStore = useOrdersStore();

const pageSize = ref<number>(10);
const currentPage = ref<number>(1);

onMounted(() => {
  const savedPage = localStorage.getItem("orders_current_page");
  const savedPageSize = localStorage.getItem("orders_page_size");
  if (savedPage) currentPage.value = parseInt(savedPage, 10) || 1;
  if (savedPageSize) pageSize.value = parseInt(savedPageSize, 10) || 10;
});

const handleUpdateCurrentPage = debounce(() => {
  localStorage.setItem("orders_current_page", String(currentPage.value));
  localStorage.setItem("orders_page_size", String(pageSize.value));
  ordersStore.fetchOrders();
}, 500);

const updateCurrentPage = () => {
  handleUpdateCurrentPage();
};

const totalPages = computed(() => {
  const size = Math.ceil(ordersStore.getTotal / pageSize.value);
  if (currentPage.value > size) {
    currentPage.value = 1;
    updateCurrentPage();
  }
  return size > 0 ? size : 1;
});

function getPagination(
  current: number,
  total: number,
  delta = 1,
): (number | "...")[] {
  const range: number[] = [];
  const rangeWithDots: (number | "...")[] = [];
  let l: number | undefined;

  for (let i = 1; i <= total; i++) {
    if (
      i === 1 ||
      i === total ||
      (i >= current - delta && i <= current + delta)
    ) {
      range.push(i);
    }
  }

  for (const i of range) {
    if (l !== undefined && i - l > 1) {
      rangeWithDots.push("...");
    }
    rangeWithDots.push(i);
    l = i;
  }

  return rangeWithDots;
}

const pages = computed(() =>
  getPagination(currentPage.value, totalPages.value),
);

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    updateCurrentPage();
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    updateCurrentPage();
  }
};

const setPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;

    updateCurrentPage();
  }
};

const handlePageSizeChange = ($event: any) => {
  pageSize.value = parseInt(($event.target as HTMLSelectElement).value, 10);
  updateCurrentPage();
};
</script>

<style scoped lang="scss">
.pagination {
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 24px;
  background: #fafbfc;
  flex: 1;
  position: absolute;
  bottom: 0;
  column-gap: 8px;

  border-radius: 8px;
  background: var(--Backgrounds-Bg-Floating, #eef2f7);
  box-shadow: 0 4px 24px 0 rgba(22, 36, 50, 0.08);

  border-top-left-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
}

.pagination-info {
  font-size: 13px;
  color: #6b7280;
}

.current-page {
  padding: 0 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 13px;
  border-color: #1ab163;
  color: #1ab163;
  background: rgba(255, 255, 255, 0.7);
  border: 2px solid #1ab163;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 4px;
}

.page-btn {
  padding: 0 8px;
  height: 24px;
  min-width: 24px;
  border: 1px solid #e5e7eb;
  cursor: pointer;
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  color: #6b7280;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.7);
  border: 2px solid rgba(22, 36, 50, 0.1);

  i {
    font-size: 8px;
  }

  &.ccb-dots {
    border: none;
    background: transparent;
    color: #6b7280;
  }
}

.page-btn:hover {
  background: #f9fafb;
  border-color: #d1d5db;
  color: #374151;
}

.page-btn.disabled {
  cursor: not-allowed;
  pointer-events: none;
}

.pagination-border {
  width: 1px;
  height: 20px;
  display: flex;
  margin: 0 8px;
  background: #dddddd;
}

.pagination-per-page {
  select {
    min-height: 24px;
    height: 24px;
    line-height: 1;
    border: 1px solid #dddddd;
    border-radius: 8px;
    font-size: 13px;
    color: #374151;
    box-shadow: none !important;
    outline: none !important;
  }
}
</style>
