export const elementById = (id: string): HTMLElement | null => document.getElementById(id);

export const querySelector = (selector: string): HTMLElement | null => document.querySelector(selector);

export const querySelectorAll = (selector: string): NodeListOf<HTMLElement> => document.querySelectorAll<HTMLElement>(selector);

export const addEvent = <K extends keyof HTMLElementEventMap>(
    el: HTMLElement | Window,
    event: K,
    callback: (ev: HTMLElementEventMap[K]) => void,
    options: boolean | AddEventListenerOptions = {},
): void => {
    el.addEventListener(event, callback as EventListener, options);
};

export const openModal = (modalId: string): void => {
    const modal = elementById(modalId);
    if (modal) modal.style.display = 'flex';
};

export const closeModal = (modalId: string): void => {
    const modal = elementById(modalId);
    if (modal) modal.style.display = 'none';
};
