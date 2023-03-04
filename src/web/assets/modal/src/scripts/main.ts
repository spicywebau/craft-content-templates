import ContentTemplate from './ContentTemplate'
import ContentTemplateSettings from './ContentTemplateSettings'

declare global {
  interface Window {
    ContentTemplates: {
      Modal?: ContentTemplatesModal
    }
  }
}

interface ContentTemplatesModal {
  contentTemplates: ContentTemplate[]
  elementId: number
}

interface ModalSettings extends Object {
  contentTemplates: ContentTemplateSettings[]
  elementId: number
}

if (typeof window.ContentTemplates === 'undefined') {
  window.ContentTemplates = {}
}

window.ContentTemplates.Modal = Garnish.Modal.extend({
  /**
   * The constructor.
   */
  init (this: ContentTemplatesModal, settings: ModalSettings): void {
    this.elementId = settings.elementId
    this.contentTemplates = settings.contentTemplates
      .map((contentTemplate) => new ContentTemplate(contentTemplate))

    console.log(this.elementId)
    this.contentTemplates.forEach((contentTemplate) => console.log(contentTemplate.getButtonHtml()))
  }
})
