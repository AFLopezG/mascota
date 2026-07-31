<template>
  <q-card flat bordered class="app-metric-card">
    <q-card-section class="q-pa-lg">
      <div class="row items-start justify-between q-gutter-md">
        <div class="col">
          <div class="text-caption text-weight-medium text-grey-6">{{ label }}</div>
          <div class="text-h4 text-weight-bold q-mt-xs">{{ value }}</div>
          <div class="text-body2 text-grey-7 q-mt-sm">{{ subtitle }}</div>
        </div>

        <q-avatar rounded size="52px" class="app-metric-card__icon">
          <q-icon :name="icon" size="26px" />
        </q-avatar>
      </div>

      <div class="row items-center q-gutter-sm q-mt-md">
        <q-badge v-if="trend" :color="trendColor" text-color="white" rounded>
          {{ trend }}
        </q-badge>
        <span v-if="hint" class="text-caption text-grey-7">{{ hint }}</span>
      </div>

      <q-linear-progress
        v-if="typeof progress === 'number'"
        :value="progress"
        rounded
        size="8px"
        color="primary"
        track-color="grey-3"
        class="q-mt-md"
      />
    </q-card-section>
  </q-card>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    default: '-'
  },
  subtitle: {
    type: String,
    default: ''
  },
  hint: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: 'sym_r_insights'
  },
  trend: {
    type: String,
    default: ''
  },
  trendColor: {
    type: String,
    default: 'primary'
  },
  progress: {
    type: Number,
    default: null
  }
})
</script>
