import PreviewImageSelectItem from './PreviewImageSelectItem'

/**
 * Class for managing the selected preview image for a content template.
 */
export default class PreviewImageSelect {
  /**
   * Container for the display of the set preview image.
   * @public
   */
  public readonly imageContainer: Element|null

  /**
   * Image for the display of the set preview image.
   * @public
   */
  public readonly image: Element|null

  /**
   * Text (filename) for the display of the set preview image.
   * @public
   */
  public readonly imageText: Element|null

  /**
   * Preview images that can be selected from the menu.
   * @public
   */
  public readonly menuItems: NodeListOf<Element>

  /**
   * The button for setting the preview image.
   * @public
   */
  public readonly btnSet: Element|null

  /**
   * The button for unsetting the preview image.
   * @public
   */
  public readonly btnRemove: Element|null

  /**
   * The hidden input for the element editor form.
   * @public
   */
  public readonly input: Element|null

  /**
   * The constructor.
   * @param container - The preview image field container.
   * @public
   */
  constructor (public readonly container: Element) {
    this.imageContainer = container.querySelector('[data-preview-image-select-show]')
    this.image = this.imageContainer?.querySelector('img') ?? null
    this.imageText = this.imageContainer?.querySelector('p') ?? null
    this.menuItems = container.querySelectorAll('[data-preview-image-select-item]')
    this.btnSet = container.querySelector('[data-preview-image-select-set]')
    this.btnRemove = container.querySelector('[data-preview-image-select-remove]')
    this.input = container.querySelector('input[name="previewImage"]')

    this.btnRemove?.addEventListener('click', (_) => this.remove())
    this.menuItems.forEach((item) => {
      const htmlItem = item as HTMLElement
      const filename = htmlItem.querySelector('span')?.textContent as string
      const url = htmlItem.querySelector('img')?.getAttribute('src') as string
      htmlItem.addEventListener('click', (_) => this.set(new PreviewImageSelectItem(filename, url)))
    })
  }

  /**
   * Sets the selected preview image.
   * @param item - A `PreviewImageSelectItem` representing the selected preview image
   * @public
   */
  public set (item: PreviewImageSelectItem): void {
    this.image?.setAttribute('src', item.url)
    this.input?.setAttribute('value', item.filename)
    this.btnRemove?.classList.remove('hidden')

    if (this.imageText !== null) {
      this.imageText.textContent = item.filename
    }

    if (this.btnSet !== null) {
      this.btnSet.textContent = Craft.t('content-templates', 'Replace')
    }
  }

  /**
   * Unsets the preview image.
   * @public
   */
  public remove (): void {
    this.image?.setAttribute('src', '')
    this.input?.setAttribute('value', '')
    this.btnRemove?.classList.add('hidden')

    if (this.imageText !== null) {
      this.imageText.textContent = Craft.t('content-templates', 'None set')
    }

    if (this.btnSet !== null) {
      this.btnSet.textContent = Craft.t('content-templates', 'Add')
    }
  }
}
