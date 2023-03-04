import ContentTemplate from './ContentTemplate'
import ContentTemplateSettings from './ContentTemplateSettings'

declare global {
  interface Window {
    ContentTemplates: {
      Modal?: typeof ContentTemplatesModal
    }
  }
}

class ContentTemplatesModal {
  public readonly contentTemplates: ContentTemplate[]
  public readonly elementId: number
  public readonly garnishModal: any

  /**
   * The constructor.
   */
  constructor (settings: ModalSettings) {
    this.elementId = settings.elementId
    this.contentTemplates = settings.contentTemplates
      .map((contentTemplate) => new ContentTemplate(contentTemplate))

    const tempHtml = this.contentTemplates
      .map((contentTemplate) => contentTemplate.getButtonHtml())
      .join('<br>')
    const $body: JQuery = $('<div class="body" />')
      .html(`${this.elementId} / ${tempHtml}`)
    const $modal: JQuery = $('<div class="modal" />')
      .append($body)
    this.garnishModal = new Garnish.Modal($modal)
  }
}

interface ModalSettings extends Object {
  contentTemplates: ContentTemplateSettings[]
  elementId: number
}

if (typeof window.ContentTemplates === 'undefined') {
  window.ContentTemplates = {}
}

window.ContentTemplates.Modal = ContentTemplatesModal
