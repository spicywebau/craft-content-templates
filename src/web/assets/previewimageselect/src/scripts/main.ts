import PreviewImageSelect from './PreviewImageSelect'
import '../styles/main.scss'

document.querySelectorAll('[data-preview-image-select]')
  .forEach((container) => new PreviewImageSelect(container))
