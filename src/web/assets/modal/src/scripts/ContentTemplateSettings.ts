import Icon from './Icon'

export default interface ContentTemplateSettings {
  id?: number
  title: string
  preview: string|Icon|null
  description: string
}
