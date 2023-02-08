import { mount } from '@vue/test-utils'
import { it, describe, expect, vi } from 'vitest';

import VueBootstrapAutocomplete from '@/components/VueBootstrapAutocomplete.vue'
import VueBootstrapAutocompleteList from '@/components/VueBootstrapAutocompleteList.vue'

describe('VueBootstrapAutocomplete', () => {
  let wrapper

  const demoData = [
    'Canada',
    'United States',
    'Mexico',
    'Japan',
    'China',
    'United Kingdom'
  ]

  beforeEach(() => {
    wrapper = mount(VueBootstrapAutocomplete, {
      propsData: {
        data: demoData
      },
	  attachTo: document.body
    })
  })

  it('Should mount and render a hidden typeahead list', () => {
    let child = wrapper.findComponent(VueBootstrapAutocompleteList)
    expect(child).toBeTruthy()
    expect(child.isVisible()).toBe(false)
  })

  it('Formats the input data properly', () => {
    expect(wrapper.vm.formattedData[0].id).toBe(0)
    expect(wrapper.vm.formattedData[0].data).toBe('Canada')
    expect(wrapper.vm.formattedData[0].text).toBe('Canada')
  })

  it('Defaults the screenReaderTextSerializer to the text for arrays', () => {
    wrapper = mount(VueBootstrapAutocomplete, {
      propsData: {
        data: ['Canada', 'CA']
      }
    })
    expect(wrapper.vm.formattedData[0].screenReaderText).toBe('Canada')
    expect(wrapper.vm.formattedData[1].screenReaderText).toBe('CA')
  })

  it('Defaults the screenReaderTextSerializer to the value of the serializer', () => {
    wrapper = mount(VueBootstrapAutocomplete, {
      propsData: {
        data: [{
          name: 'Canada',
          code: 'CA'
        }],
        value: 'Can',
        serializer: t => t.name
      }
    })
    expect(wrapper.vm.formattedData[0].id).toBe(0)
    expect(wrapper.vm.formattedData[0].data.code).toBe('CA')
    expect(wrapper.vm.formattedData[0].screenReaderText).toBe('Canada')
  })

  it('Uses a custom screenReaderTextSerializer properly', () => {
    wrapper = mount(VueBootstrapAutocomplete, {
      propsData: {
        data: [{
          name: 'Canada',
          screenReaderText: 'Canada button',
          code: 'CA'
        }],
        value: 'Can',
        screenReaderTextSerializer: t => t.screenReaderText,
        serializer: t => t.name
      }
    })
    expect(wrapper.vm.formattedData[0].id).toBe(0)
    expect(wrapper.vm.formattedData[0].data.code).toBe('CA')
    expect(wrapper.vm.formattedData[0].screenReaderText).toBe('Canada button')
  })

  it('Uses a custom serializer properly', () => {
    wrapper = mount(VueBootstrapAutocomplete, {
      propsData: {
        data: [{
          name: 'Canada',
          code: 'CA'
        }],
        value: 'Can',
        serializer: t => t.name
      }
    })
    expect(wrapper.vm.formattedData[0].id).toBe(0)
    expect(wrapper.vm.formattedData[0].data.code).toBe('CA')
    expect(wrapper.vm.formattedData[0].text).toBe('Canada')
  })

  it('Allows for a name to be provided for the input', () => {
    wrapper = mount(VueBootstrapAutocomplete, {
      propsData: {
        data: demoData,
        inputName: 'name-is-provided-for-this-input'
      }
    })
    expect(wrapper.find('input').attributes().name).toBe('name-is-provided-for-this-input')
  })

  it('Show the list when given a query', async () => {
    let child = wrapper.findComponent(VueBootstrapAutocompleteList)
    expect(child.isVisible()).toBe(false)
    wrapper.find('input').setValue('Can')
    await wrapper.vm.$nextTick()
    expect(child.isVisible()).toBe(true)
  })

  it('Hides the list when focus is lost', async () => {
    let child = wrapper.findComponent(VueBootstrapAutocompleteList)
    wrapper.setData({ inputValue: 'Can' })
    wrapper.find('input').trigger('focus')
    await wrapper.vm.$nextTick()
    expect(child.isVisible()).toBe(true)

    wrapper.find('input').trigger('blur')
    await wrapper.vm.$nextTick()
    expect(child.isVisible()).toBe(false)
  })

  it('Renders the list in different sizes', async () => {
    expect(wrapper.vm.inputGroupClasses).toBe('input-group')
    wrapper.setProps({
      size: 'lg'
    })
	await wrapper.vm.$nextTick()
    expect(wrapper.vm.inputGroupClasses).toBe('input-group input-group-lg')
  })

  describe('key press handling', () => {
    it('Emits a keyup.enter event when enter is pressed on the input field', () => {
      let input = wrapper.find('input')
      input.trigger('keyup.enter')
      expect(wrapper.emitted().keyup).toBeTruthy()
      expect(wrapper.emitted().keyup.length).toBe(1)
      expect(wrapper.emitted().keyup[0][0].keyCode).toBe(13)
    })

/* is this needed?
    it('triggers the correct event when hitting enter', () => {
      let child = wrapper.findComponent(VueBootstrapAutocompleteList)
      const hitActive = vi.spyOn(child.vm, 'hitActiveListItem')
      let input = wrapper.find('input')

      input.trigger('keyup.enter')

      expect(hitActive).toHaveBeenCalled()
    })

    it('triggers the correct event when hitting the down arrow', () => {
      let child = wrapper.findComponent(VueBootstrapAutocompleteList)
      const selectNextListItem = vi.spyOn(child.vm, 'selectNextListItem')
      let input = wrapper.find('input')

      input.trigger('keyup.down')

      expect(selectNextListItem).toHaveBeenCalled()
    })

    it('triggers the correct event when hitting up arrow', () => {
      let child = wrapper.findComponent(VueBootstrapAutocompleteList)
      const selectPreviousListItem = vi.spyOn(child.vm, 'selectPreviousListItem')
      let input = wrapper.find('input')

      input.trigger('keyup.up')

      expect(selectPreviousListItem).toHaveBeenCalled()
    })
*/
    it('Emits a blur event when the underlying input field blurs', async () => {
      let input = wrapper.find('input')
      await input.trigger('blur')
      expect(wrapper.emitted().blur).toBeTruthy()
    })

    it('Does not emit a blur event if the focus shifted to the dropdown list', async () => {
      let input = wrapper.find('input')
      wrapper.setData({ inputValue: 'Can' })
      await input.trigger('focus')

      let listItem = wrapper.get('.vbst-item').element
      await input.trigger('blur', { relatedTarget: listItem })

      expect(wrapper.emitted().blur).toBeFalsy()
    })

    it('Emits a focus event when the underlying input field receives focus', async () => {
      let input = wrapper.find('input')
      await input.trigger('focus')
      expect(wrapper.emitted().focus).toBeTruthy()
    })

    it('Emits a paste event when the underlying input field receives a paste event', async () => {
      let input = wrapper.find('input')
      await input.trigger('paste')
      expect(wrapper.emitted().paste).toBeTruthy()
    })

  })

  describe('No results info prop and template', () => {
    it('Shows a "No results found for Canada" when the noResultsInfo prop has been set and there are no results', async () => {
      wrapper = mount({
        components: {
          VueBootstrapAutocomplete
        },
        data() {
          return {
            data: demoData,
            query: ''
          }
        },
        template: `
          <vue-bootstrap-autocomplete
            :data="data"
            v-model="query"
            no-results-info="No results found"
          />
        `
      })

      const child = wrapper.findComponent(VueBootstrapAutocompleteList)

      expect(child.isVisible()).toBe(false)
      wrapper.find('input').setValue('Cadana')
      await wrapper.vm.$nextTick()

      expect(child.isVisible()).toBe(true)

      expect(wrapper.vm.$data.query).toBe('Cadana')
      expect(child.text()).toBe('No results found')
    })

    it('Does not show a "No results found" message when there are results and the noResultsInfo prop has been set', async () => {
      wrapper = mount({
        components: {
          VueBootstrapAutocomplete
        },
        data() {
          return {
            data: demoData,
            query: ''
          }
        },
        template: `
          <vue-bootstrap-autocomplete
            :data="data"
            v-model="query"
            no-results-info="No results found"
          />
          </vue-bootstrap-autocomplete>
        `
      })

      const child = wrapper.findComponent(VueBootstrapAutocompleteList)

      expect(child.isVisible()).toBe(false)
      wrapper.find('input').setValue('Canada')

      await wrapper.vm.$nextTick()
      expect(child.isVisible()).toBe(true)
      expect(wrapper.vm.$data.query).toBe('Canada')
      expect(child.text()).not.toBe('No results found')
    })

    it('Shows a "No results found for Cadana" when the noResultsInfo template has been supplied', async () => {
      wrapper = mount({
        components: {
          VueBootstrapAutocomplete
        },
        data() {
          return {
            data: demoData,
            query: ''
          }
        },
        template: `
          <vue-bootstrap-autocomplete
            :data="data"
            v-model="query"
          >
            <template #noResultsInfo>
              <span>No results for {{ query }}</span>
            </template>
          </vue-bootstrap-autocomplete>
        `
      })

      const child = wrapper.findComponent(VueBootstrapAutocompleteList)
      const slot = wrapper.find('#noResultsInfo')

      expect(child.isVisible()).toBe(false)
      expect(slot.isVisible()).toBe(false)
      wrapper.find('input').setValue('Cadana')

      await wrapper.vm.$nextTick()
      expect(child.isVisible()).toBe(true)
      expect(slot.isVisible()).toBe(true)
      expect(wrapper.vm.$data.query).toBe('Cadana')
      expect(slot.text()).toBe('No results for Cadana')
      expect(child.text()).toBe('No results for Cadana')
    })

    it('Does not show a "No results found" message when there are results and the noResultsInfo template has been set', async () => {
      wrapper = mount({
        components: {
          VueBootstrapAutocomplete
        },
        data() {
          return {
            data: demoData,
            query: ''
          }
        },
        template: `
          <vue-bootstrap-autocomplete
            :data="data"
            v-model="query"
          >
            <template #noResultsInfo>
              <span>No results for {{ query }}</span>
            </template>
          </vue-bootstrap-autocomplete>
        `
      })

      const child = wrapper.findComponent(VueBootstrapAutocompleteList)
      const slot = wrapper.find('#noResultsInfo')

      expect(child.isVisible()).toBe(false)
      expect(slot.isVisible()).toBe(false)
      wrapper.find('input').setValue('Can')

      await wrapper.vm.$nextTick()

      expect(child.isVisible()).toBe(true)
      expect(wrapper.vm.$data.query).toBe('Can')
      expect(child.html()).not.toContain('No results for')
      expect(child.text()).not.toBe('No results for Canada')
    })

    it('Does not show "No results found" message when the noResultsInfo template and prop have not been set', async () => {
      let child = wrapper.findComponent(VueBootstrapAutocompleteList)

      expect(child.isVisible()).toBe(false)
      wrapper.find('input').setValue('Canada')
      await wrapper.vm.$nextTick()

      expect(wrapper.props().noResultsInfo).toBe(undefined)
      expect(wrapper.find('#noResultsInfo').exists()).toBe(false)
    })

    it('Only shows the template with "No results found for Cadana" when both the noResultsInfo template and prop have been supplied', async () => {
      wrapper = mount({
        components: {
          VueBootstrapAutocomplete
        },
        data() {
          return {
            data: demoData,
            query: ''
          }
        },
        template: `
          <vue-bootstrap-autocomplete
            :data="data"
            v-model="query"
            no-results-info="No results found using the prop"
          >
            <template #noResultsInfo>
              <span>No results found using the template</span>
            </template>
          </vue-bootstrap-autocomplete>
        `
      })

      const child = wrapper.findComponent(VueBootstrapAutocompleteList)
      const slot = wrapper.find('#noResultsInfo')

      expect(child.isVisible()).toBe(false)
      expect(slot.isVisible()).toBe(false)
      wrapper.find('input').setValue('Cadana')

      await wrapper.vm.$nextTick()
      expect(child.isVisible()).toBe(true)
      expect(slot.isVisible()).toBe(true)
      expect(wrapper.vm.$data.query).toBe('Cadana')
      expect(slot.text()).toBe('No results found using the template')
      expect(child.text()).toBe('No results found using the template')
      expect(wrapper.text()).not.toContain('No results found using the prop')
    })

    it('Shows the "No results found" message when the noResultsInfo prop has been set and there is no data', async () => {
      wrapper = mount({
        components: {
          VueBootstrapAutocomplete
        },
        data() {
          return {
            data: [],
            query: ''
          }
        },
        template: `
          <vue-bootstrap-autocomplete
            :data="data"
            v-model="query"
            no-results-info="No results found"
          />
          </vue-bootstrap-autocomplete>
        `
      })

      const child = wrapper.findComponent(VueBootstrapAutocompleteList)

      expect(child.isVisible()).toBe(false)
      wrapper.find('input').setValue('Cadana')

      await wrapper.vm.$nextTick()
      expect(child.isVisible()).toBe(true)
      expect(wrapper.vm.$data.query).toBe('Cadana')
      expect(child.text()).toBe('No results found')
    });

    it('Shows the "No results found" message when the noResultsInfo slot has been set and there is no data', async () => {
      wrapper = mount({
        components: {
          VueBootstrapAutocomplete
        },
        data() {
          return {
            data: [],
            query: ''
          }
        },
        template: `
          <vue-bootstrap-autocomplete
            :data="data"
            v-model="query"
          >
            <template #noResultsInfo>
              <span>No results found</span>
            </template>
          </vue-bootstrap-autocomplete>
        `
      })

      const child = wrapper.findComponent(VueBootstrapAutocompleteList)
      const slot = wrapper.find('#noResultsInfo')

      expect(child.isVisible()).toBe(false)
      expect(slot.isVisible()).toBe(false)
      wrapper.find('input').setValue('Cadana')

      await wrapper.vm.$nextTick()
      expect(child.isVisible()).toBe(true)
      expect(slot.isVisible()).toBe(true)
      expect(wrapper.vm.$data.query).toBe('Cadana')
      expect(slot.text()).toBe('No results found')
      expect(child.text()).toBe('No results found')
    });
	  
  });

})
