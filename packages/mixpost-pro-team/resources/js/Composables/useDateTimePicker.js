import useSettings from '@/Composables/useSettings.js'
import { useI18n } from 'vue-i18n'

const useDateTimePicker = () => {
  const { t: $t } = useI18n()
  const { weekStartsOn } = useSettings()

  const getLocaleConfig = () => {
    return {
      firstDayOfWeek: weekStartsOn,
      weekdays: {
        shorthand: [
          $t('calendar.weekdays.sunday.short'),
          $t('calendar.weekdays.monday.short'),
          $t('calendar.weekdays.tuesday.short'),
          $t('calendar.weekdays.wednesday.short'),
          $t('calendar.weekdays.thursday.short'),
          $t('calendar.weekdays.friday.short'),
          $t('calendar.weekdays.saturday.short')
        ],
        longhand: [
          $t('calendar.weekdays.sunday.full'),
          $t('calendar.weekdays.monday.full'),
          $t('calendar.weekdays.tuesday.full'),
          $t('calendar.weekdays.wednesday.full'),
          $t('calendar.weekdays.thursday.full'),
          $t('calendar.weekdays.friday.full'),
          $t('calendar.weekdays.saturday.full')
        ]
      },
      months: {
        shorthand: [
          $t('calendar.months.january.short'),
          $t('calendar.months.february.short'),
          $t('calendar.months.march.short'),
          $t('calendar.months.april.short'),
          $t('calendar.months.may.short'),
          $t('calendar.months.june.short'),
          $t('calendar.months.july.short'),
          $t('calendar.months.august.short'),
          $t('calendar.months.september.short'),
          $t('calendar.months.october.short'),
          $t('calendar.months.november.short'),
          $t('calendar.months.december.short')
        ],
        longhand: [
          $t('calendar.months.january.full'),
          $t('calendar.months.february.full'),
          $t('calendar.months.march.full'),
          $t('calendar.months.april.full'),
          $t('calendar.months.may.full'),
          $t('calendar.months.june.full'),
          $t('calendar.months.july.full'),
          $t('calendar.months.august.full'),
          $t('calendar.months.september.full'),
          $t('calendar.months.october.full'),
          $t('calendar.months.november.full'),
          $t('calendar.months.december.full')
        ]
      }
    }
  }

  const getPrevArrow = () => {
    return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>'
  }

  const getNextArrow = () => {
    return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>'
  }

  return {
    getLocaleConfig,
    getPrevArrow,
    getNextArrow
  }
}

export default useDateTimePicker
