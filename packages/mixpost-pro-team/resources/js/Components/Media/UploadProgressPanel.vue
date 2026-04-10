<script setup>
import UploadProgressItem from '@/Components/Media/UploadProgressItem.vue'
import PureButton from '@/Components/Button/PureButton.vue'
import XIcon from '@/Icons/X.vue'
import Panel from '@/Components/Surface/Panel.vue'

defineProps({
  jobs: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['cancel', 'cancelAll'])
</script>
<template>
  <Teleport to="body">
    <div v-if="jobs.length > 0" class="fixed bottom-md right-md z-50 w-80">
      <Panel :with-padding="false" class="overflow-hidden">
        <div
          v-if="jobs.length > 1"
          class="flex items-center justify-end px-md py-sm border-b border-gray-100"
        >
          <PureButton :destructive-on-hover="true" class="text-sm" @click="emit('cancelAll')">
            <template #icon>
              <XIcon class="!w-4 !h-4" />
            </template>
            Cancel all
          </PureButton>
        </div>
        <div class="max-h-64 overflow-y-auto flex flex-col gap-xs">
          <UploadProgressItem
            v-for="job in jobs"
            :key="job.id"
            :file-name="job.file.name"
            :progress="job.progress"
            :status="job.status"
            :error="job.error"
            @cancel="emit('cancel', job)"
          />
        </div>
      </Panel>
    </div>
  </Teleport>
</template>
