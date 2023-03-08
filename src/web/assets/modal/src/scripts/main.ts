import ContentTemplate from './ContentTemplate'
import ContentTemplateSettings from './ContentTemplateSettings'
import '../styles/main.scss'

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
    // Always start off with a blank one
    this.contentTemplates = [
      new ContentTemplate({
        title: Craft.t('content-templates', 'Blank'),
        description: Craft.t('content-templates', 'Start off with a clean slate.')
      })
    ]
    this.contentTemplates.push(
      ...settings.contentTemplates
        .map((contentTemplate) => new ContentTemplate(contentTemplate))
    )

    const $modal: JQuery = $('<div class="modal ct_modal" />')
      .append($('<div class="spinner big">'))
    this.garnishModal = new Garnish.Modal($modal)
    const $body: JQuery = $('<div class="body" />')
      .appendTo($modal)
      .append($('<h2 class="ct_heading" />').text(Craft.t('content-templates', 'Choose a template')))
    const $contentTemplatesContainer = $('<div class="ct_container" />')
      .appendTo($body)
    this.contentTemplates.forEach((contentTemplate) => {
      $('<div class="ct_template" />')
        .append(contentTemplate.$button)
        .appendTo($contentTemplatesContainer)
      contentTemplate.$button.on('activate', (_: JQuery.Event) => {
        if (typeof contentTemplate.id === 'undefined') {
          // The blank option
          this.garnishModal.hide()
        } else {
          $modal.addClass('applying')
          const data = {
            elementId: this.elementId,
            contentTemplateId: contentTemplate.id
          }
          Craft.sendActionRequest('POST', 'content-templates/cp/apply', { data })
            .then((response) => {
              window.location.href = response.data.redirect
            })
            .catch(response => {
              $modal.removeClass('applying')
              Craft.cp.displayError(
                response.error ?? Craft.t('content-templates', 'An unknown error occurred.')
              )
            })
        }
      })
    })
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
