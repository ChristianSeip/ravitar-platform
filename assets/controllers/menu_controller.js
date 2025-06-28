import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
	static targets = ['panel']

	toggle() {
		const panel = this.panelTarget
		const isHidden = panel.classList.contains('hidden')

		if (isHidden) {
			panel.classList.remove('hidden')
			requestAnimationFrame(() => {
				panel.classList.remove('translate-y-[-10px]', 'opacity-0')
				panel.classList.add('translate-y-0', 'opacity-100')
			})
		} else {
			panel.classList.add('translate-y-[-10px]', 'opacity-0')
			panel.classList.remove('translate-y-0', 'opacity-100')
			setTimeout(() => {
				panel.classList.add('hidden')
			}, 300)
		}
	}
}
