<template>
  <div class="ccb-notes-wrapper">
    <div class="notes-header">
      <span>Order note</span>
    </div>
    <div class="notes-list">
      <div class="notes-add-note">
        <button class="ccb-add-note-btn" @click="isAddNote = true">
          Add note
        </button>
        <div class="notes-add-note-content" v-if="isAddNote">
          <textarea v-model="noteContent" placeholder="Add note"></textarea>
          <span class="notes-add-note-actions">
            <button
              class="ccb-add-note-btn ccb-add-note-btn-cancel"
              @click="isAddNote = false"
            >
              Cancel
            </button>
            <button
              class="ccb-add-note-btn ccb-add-note-btn-save"
              @click="addNote"
            >
              Save
            </button>
          </span>
        </div>
      </div>
      <div class="notes-list-items">
        <div class="notes-list-item" v-for="note in notes">
          <span class="notes-list-item-content">{{ note.content }}</span>
          <span class="notes-list-item-footer">
            <span class="notes-created-at">{{ note.created_at }}</span>
            <span class="notes-actions">
              <i
                class="ccb-icon-Path-3483 ccb-edit-btn"
                @click="selectNote(note)"
              ></i>
              <i
                class="ccb-icon-Path-3503 ccb-delete-btn"
                @click="deleteNote(note.id)"
              ></i>
            </span>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, ref } from "vue";
import { IOrderNote } from "@/orders/shared/types/orders.type";

type Props = {
  notes: IOrderNote[];
};

const props = defineProps<Props>();
const { notes } = toRefs(props);

const emit = defineEmits<{
  (e: "create-note", content: string): void;
  (e: "update-note", note_id: number, content: string): void;
  (e: "delete-note", note_id: number): void;
}>();

const selectedNote = ref<IOrderNote | null>(null);
const noteContent = ref<string>("");
const isAddNote = ref<boolean>(false);

const selectNote = (note: IOrderNote) => {
  isAddNote.value = true;
  selectedNote.value = note;
  noteContent.value = note.content;
};

const addNote = () => {
  if (noteContent.value.trim()) {
    if (selectedNote.value) {
      emit("update-note", selectedNote.value.id, noteContent.value);
    } else {
      emit("create-note", noteContent.value);
    }
    noteContent.value = "";
    isAddNote.value = false;
  }
};

const deleteNote = (note_id: number) => {
  emit("delete-note", note_id);
};
</script>

<style scoped lang="scss">
.ccb-notes-wrapper {
  display: flex;
  flex-direction: column;
  row-gap: 10px;

  .notes-header {
    span {
      color: #162432;
      font-size: 13px;
      font-weight: 700;
      line-height: 1;
    }
  }

  .notes-list {
    display: flex;
    flex-direction: column;
    row-gap: 10px;

    .notes-list-items {
      display: flex;
      flex-direction: column;
      row-gap: 10px;
    }

    .notes-list-item {
      display: flex;
      width: 100%;
      flex-direction: column;
      row-gap: 12px;
      padding: 10px;
      border-radius: 12px;
      background: #fff8cc;

      .notes-list-item-content {
        padding: 10px 0;
        font-size: 14px;
        font-weight: 500;
        line-height: 1;
        color: rgba(22, 36, 50, 0.85);
      }

      .notes-list-item-footer {
        display: flex;
        width: 100%;
        justify-content: space-between;
        align-items: center;
      }

      .notes-created-at {
        color: rgba(22, 36, 50, 0.5);
        font-size: 12px;
        font-weight: 500;
        line-height: 1;
      }

      .notes-actions {
        display: flex;
        column-gap: 15px;

        i {
          cursor: pointer;
          transition: all 0.2s linear;
          font-size: 14px;
          font-weight: 500;
          line-height: 1;
        }

        .ccb-edit-btn {
          color: rgba(22, 36, 50, 0.3);

          &:hover {
            color: rgba(22, 36, 50, 0.5);
          }
        }

        .ccb-delete-btn {
          color: #d94141;

          &:hover {
            color: #b33333;
          }
        }
      }
    }
  }
}

.notes-add-note {
  display: flex;
  flex-direction: column;
  row-gap: 10px;

  &-actions {
    display: flex;
    column-gap: 10px;
    justify-content: flex-end;

    .ccb-add-note-btn {
      width: max-content;
      padding: 0 6px;
      height: 24px;
      background: transparent;
      border-radius: 8px;
      border: 2px solid rgba(22, 36, 50, 0.05);

      color: rgba(22, 36, 50, 0.85);
      font-size: 12px;
      font-weight: 500;
      line-height: 1;
      cursor: pointer;
      transition: all 0.2s linear;

      &.ccb-add-note-btn-save {
        background: rgba(22, 36, 50, 0.05);
      }

      &:hover {
        color: rgba(22, 36, 50, 1);
        border-color: rgba(22, 36, 50, 0.1);
      }
    }
  }

  &-content {
    display: flex;
    flex-direction: column;
    row-gap: 10px;
    padding: 10px;
    border-radius: 12px;
    background: #fff8cc;

    textarea {
      width: 100%;
      height: 80px;
      outline: none !important;
      box-shadow: none !important;
      padding: 10px;
      resize: vertical;
      border-radius: 4px;
      border: 1px dashed rgba(22, 36, 50, 0.1);
      background: #fff;
    }
  }

  button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 40px;
    border-radius: 8px;
    background: #fff8cc;
    color: rgba(22, 36, 50, 0.85);
    font-size: 14px;
    font-weight: 500;
    line-height: 1;
    cursor: pointer;
    transition: all 0.2s linear;
    border: none;
    outline: none;
    padding: 0;

    &:hover {
      color: rgba(22, 36, 50, 1);
    }
  }
}
</style>
