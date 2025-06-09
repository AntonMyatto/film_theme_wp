export class CustomSelect {
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            onChange: () => {},
            ...options
        };
        this.isOpen = false;
        this.selectedValue = '';
        this.init();
    }

    init() {
        this.createSelectStructure();
        
        this.bindEvents();

        const defaultOption = this.element.querySelector('option[selected]');
        if (defaultOption) {
            this.setSelectedValue(defaultOption.value, defaultOption.textContent);
        }
    }

    createSelectStructure() {
        this.originalSelect = this.element;
        this.originalSelect.style.display = 'none';

        this.customSelect = document.createElement('div');
        this.customSelect.className = 'custom-select';

        this.trigger = document.createElement('div');
        this.trigger.className = 'custom-select__trigger';
        this.triggerText = document.createElement('span');
        this.trigger.appendChild(this.triggerText);

        this.optionsList = document.createElement('div');
        this.optionsList.className = 'custom-select__options';

        Array.from(this.originalSelect.options).forEach(option => {
            const optionElement = document.createElement('div');
            optionElement.className = 'custom-select__option';
            optionElement.dataset.value = option.value;
            optionElement.textContent = option.textContent;
            if (option.selected) {
                optionElement.classList.add('selected');
                this.triggerText.textContent = option.textContent;
            }
            this.optionsList.appendChild(optionElement);
        });

        this.customSelect.appendChild(this.trigger);
        this.customSelect.appendChild(this.optionsList);

        this.originalSelect.parentNode.insertBefore(this.customSelect, this.originalSelect.nextSibling);
    }

    bindEvents() {
        this.trigger.addEventListener('click', () => this.toggleSelect());

        this.optionsList.addEventListener('click', (e) => {
            const option = e.target.closest('.custom-select__option');
            if (option) {
                const value = option.dataset.value;
                const text = option.textContent;
                this.setSelectedValue(value, text);
                this.closeSelect();
                this.options.onChange(value);
            }
        });

        document.addEventListener('click', (e) => {
            if (!this.customSelect.contains(e.target)) {
                this.closeSelect();
            }
        });
    }

    toggleSelect() {
        this.isOpen ? this.closeSelect() : this.openSelect();
    }

    openSelect() {
        this.isOpen = true;
        this.trigger.classList.add('active');
        this.optionsList.classList.add('active');
    }

    closeSelect() {
        this.isOpen = false;
        this.trigger.classList.remove('active');
        this.optionsList.classList.remove('active');
    }

    setSelectedValue(value, text) {
        this.selectedValue = value;
        this.triggerText.textContent = text;
        
        this.originalSelect.value = value;

        const options = this.optionsList.querySelectorAll('.custom-select__option');
        options.forEach(option => {
            option.classList.toggle('selected', option.dataset.value === value);
        });
    }

    getValue() {
        return this.selectedValue;
    }
} 