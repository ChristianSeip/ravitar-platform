// assets/controllers/search_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
	static targets = ['form'];

	toggle(event) {
		// Verhindert das Standard-Verhalten eines <a href="#"> oder <button type="submit">
		if (event) event.preventDefault();

		this.formTarget.classList.toggle('hidden');
		if (!this.formTarget.classList.contains('hidden')) {
			this.formTarget.querySelector('input')?.focus();
		}
	}
}
