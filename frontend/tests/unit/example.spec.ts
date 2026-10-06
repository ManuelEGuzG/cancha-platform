import { mount } from '@vue/test-utils'
import HomePage from '@/views/HomePage.vue'
import { describe, expect, test } from 'vitest'

describe('HomePage.vue', () => {
  test('renders the Sportra home page', () => {
    const wrapper = mount(HomePage)
    expect(wrapper.text()).toContain('DOMINA LA CANCHA HOY')
  })
})
