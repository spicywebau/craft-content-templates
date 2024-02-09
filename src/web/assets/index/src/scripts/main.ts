/*
This file is based on `Craft.EntryIndex` from Craft CMS 4.3.10, by Pixel & Tonic, Inc.
https://github.com/craftcms/cms/blob/4.3.10/src/web/assets/cp/src/js/EntryIndex.js
Craft CMS is released under the terms of the Craft License, a copy of which is included below.
https://github.com/craftcms/cms/blob/4.3.10/LICENSE.md

Copyright © Pixel & Tonic

Permission is hereby granted to any person obtaining a copy of this software
(the “Software”) to use, copy, modify, merge, publish and/or distribute copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

1. **Don’t plagiarize.** The above copyright notice and this license shall be
   included in all copies or substantial portions of the Software.

2. **Don’t use the same license on more than one project.** Each licensed copy
   of the Software shall be actively installed in no more than one production
   environment at a time.

3. **Don’t mess with the licensing features.** Software features related to
   licensing shall not be altered or circumvented in any way, including (but
   not limited to) license validation, payment prompts, feature restrictions,
   and update eligibility.

4. **Pay up.** Payment shall be made immediately upon receipt of any notice,
   prompt, reminder, or other message indicating that a payment is owed.

5. **Follow the law.** All use of the Software shall not violate any applicable
   law or regulation, nor infringe the rights of any other person or entity.

Failure to comply with the foregoing conditions will automatically and
immediately result in termination of the permission granted hereby. This
license does not include any right to receive updates to the Software or
technical support. Licensees bear all risk related to the quality and
performance of the Software and any modifications made or obtained to it,
including liability for actual and consequential harm, such as loss or
corruption of data, and any necessary service, repair, or correction.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES, OR OTHER
LIABILITY, INCLUDING SPECIAL, INCIDENTAL AND CONSEQUENTIAL DAMAGES, WHETHER IN
AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

declare const ContentTemplates: {
  IndexSettings: {
    entryTypes: EntryType[]
  }
}

interface ContentTemplateIndexInterface extends BaseElementIndexInterface {
  _createContentTemplate: (entryTypeId: number) => void
  _entryTypes: EntryType[]
  _menu: any
  $newTemplateBtn: JQuery|null
  $newTemplateBtnGroup: JQuery|null
  addListener: (elem: HTMLElement|JQuery, events: string|string[], data: object|Function|string, func?: Function|string) => void
}

const ContentTemplateIndex = Craft.BaseElementIndex.extend({
  $newTemplateBtnGroup: null,
  $newTemplateBtn: null,

  init (elementType: string, $container: JQuery, settings: object) {
    this.on('selectSource', this.updateButton.bind(this))
    this.on('selectSite', this.updateButton.bind(this))
    this.base(elementType, $container, settings)
  },

  afterInit () {
    // Set our local entry type data
    this._entryTypes = ContentTemplates.IndexSettings.entryTypes
    this.base()
  },

  createView (mode: string, settings: object) {
    // Remove any structure update listeners on the old view
    this.view?.structureTableSort?.off('positionChange')

    // Listen for structure updates on the new view
    const newView = this.base(mode, settings)
    newView.structureTableSort?.on('positionChange', (e: any) => {
      // Send new structure data to server, which will update the project config
      const data = {
        type: e.target.tableView.elementIndex.$source.data('key').substring(10),
        elementIds: e.target.$items.get().map((item: HTMLElement) => item.getAttribute('data-id'))
      }
      Craft.sendActionRequest('POST', 'content-templates/cp/save-config-order', { data })
        .catch((_) => console.warn('Unable to update project config for content template order'))
    })

    return newView
  },

  updateButton (this: ContentTemplateIndexInterface) {
    if (this.$source === null) {
      return
    }

    const handle: string = this.$source.data('handle') as string
    const selectedEntryType = this._entryTypes.find((entryType: EntryType) => entryType.handle === handle)

    if (typeof selectedEntryType === 'undefined') {
      throw new Error(`Element index source handle "${handle}" is invalid`)
    }

    // Update the New Template button
    if (this.$newTemplateBtnGroup !== null) {
      this.$newTemplateBtnGroup.remove()
    }

    this.$newTemplateBtnGroup = $('<div class="btngroup submit" data-wrapper/>')
    let $menuBtn: JQuery|null = null
    const menuId = 'new-content-template-menu-' + Craft.randomString(10)
    const visibleLabel =
      this.settings.context === 'index'
        ? Craft.t('content-templates', 'New content template')
        : Craft.t('content-templates', 'New {entryType} content template', {
          entryType: selectedEntryType.name
        })

    const ariaLabel =
      this.settings.context === 'index'
        ? Craft.t('content-templates', 'New content template of the {entryType} type', {
          entryType: selectedEntryType.name
        })
        : visibleLabel

    // In index contexts, the button functions as a link
    // In non-index contexts, the button triggers a slideout editor
    const role = this.settings.context === 'index' ? 'link' : null

    this.$newTemplateBtn = Craft.ui
      .createButton({
        label: visibleLabel,
        ariaLabel: ariaLabel,
        spinner: true,
        role: role
      })
      .addClass('submit add icon')
      .appendTo(this.$newTemplateBtnGroup) as JQuery<HTMLElement>

    this.addListener(this.$newTemplateBtn, 'click mousedown', (ev: JQuery.ClickEvent|JQuery.MouseDownEvent) => {
      // If this is the element index, check for Ctrl+clicks and middle button clicks
      if (
        this.settings.context === 'index' &&
        ((ev.type === 'click' && Garnish.isCtrlKeyPressed(ev)) ||
          (ev.type === 'mousedown' && ev.originalEvent?.button === 1))
      ) {
        window.open(Craft.getUrl(`content-templates/${selectedEntryType.id}/new`))
      } else if (ev.type === 'click') {
        this._createContentTemplate(selectedEntryType.id)
      }
    })

    if (this._entryTypes.length > 1) {
      $menuBtn = $('<button/>', {
        type: 'button',
        class: 'btn submit menubtn btngroup-btn-last',
        'aria-controls': menuId,
        'data-disclosure-trigger': '',
        'aria-label': Craft.t('content-templates', 'New content template, choose an entry type')
      }).appendTo(this.$newTemplateBtnGroup)
    }

    this.addButton(this.$newTemplateBtnGroup)

    if ($menuBtn !== null) {
      const $menuContainer = $('<div/>', {
        id: menuId,
        class: 'menu menu--disclosure'
      }).appendTo(this.$newTemplateBtnGroup)
      const $ul = $('<ul/>').appendTo($menuContainer)

      this._entryTypes.forEach((entryType: EntryType) => {
        const anchorRole = this.settings.context === 'index' ? 'link' : 'button'
        if (
          // TODO
          // (this.settings.context === 'index' && (entryType.section?.sites.includes(this.siteId) ?? false)) ||
          this.settings.context === 'index' ||
          (this.settings.context !== 'index' && entryType !== selectedEntryType)
        ) {
          const $li = $('<li/>').appendTo($ul)
          const $a = $('<a/>', {
            role: anchorRole === 'button' ? 'button' : null,
            href: '#', // Allows for click listener and tab order
            type: anchorRole === 'button' ? 'button' : null,
            text: Craft.t('content-templates', 'New {entryType} content template', {
              entryType: entryType.name
            })
          }).appendTo($li)
          this.addListener($a, 'click', () => {
            $menuBtn?.data('trigger').hide()
            this._createContentTemplate(entryType.id)
          })

          if (anchorRole === 'button') {
            this.addListener($a, 'keydown', (event: KeyboardEvent) => {
              if (event.keyCode === Garnish.SPACE_KEY) {
                event.preventDefault()
                $menuBtn?.data('trigger').hide()
                this._createContentTemplate(entryType.id)
              }
            })
          }
        }
      })

      this._menu = new Garnish.DisclosureMenu($menuBtn)
    }

    // Update the URL if we're on the Content Templates index
    // ---------------------------------------------------------------------

    if (this.settings.context === 'index') {
      let uri = 'content-templates'

      if (typeof handle !== 'undefined') {
        uri += '/' + handle
      }

      Craft.setPath(uri)
    }
  },

  _createContentTemplate (this: ContentTemplateIndexInterface, entryTypeId: number) {
    if (this.$newTemplateBtn?.hasClass('loading') ?? false) {
      console.warn('New content template creation already in progress.')
      return
    }

    // Find the entry type
    const entryType = this._entryTypes.find((entryType: EntryType) => entryType.id === entryTypeId)

    if (typeof entryType === 'undefined') {
      throw new Error(`Invalid entry type ID: ${entryTypeId}`)
    }

    this.$newTemplateBtn?.addClass('loading')

    Craft.sendActionRequest('POST', 'content-templates/cp/create', {
      data: {
        siteId: this.siteId,
        entryType: entryType.handle
      }
    })
      .then(({ data }) => {
        document.location.href = Craft.getUrl(data.cpEditUrl, { fresh: 1 })
      })
      .finally(() => {
        this.$newTemplateBtn?.removeClass('loading')
      })
  }
})

// Register it!
Craft.registerElementIndexClass('spicyweb\\contenttemplates\\elements\\ContentTemplate', ContentTemplateIndex)
