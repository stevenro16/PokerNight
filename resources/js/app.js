import Alpine from 'alpinejs';
import Persist from '@alpinejs/persist';

Alpine.plugin(Persist);

Alpine.data('imageUpload', () => ({
    previews: [],
    dragging: false,

    handleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.size > 10 * 1024 * 1024) {
                alert(`${file.name} exceeds 10 MB limit.`);
                return;
            }
            if (!file.type.startsWith('image/')) {
                alert(`${file.name} is not an image.`);
                return;
            }
            const reader = new FileReader();
            reader.onload = e => this.previews.push({ name: file.name, url: e.target.result });
            reader.readAsDataURL(file);
        });
    },

    onDrop(e) {
        this.dragging = false;
        this.handleFiles(e.dataTransfer.files);
        const input = this.$refs.fileInput;
        if (input) {
            const dt = new DataTransfer();
            e.dataTransfer.files.forEach(f => dt.items.add(f));
            input.files = dt.files;
        }
    },

    removePreview(index) {
        this.previews.splice(index, 1);
    },
}));

Alpine.data('pokerCalendar', (eventDates, baseUrl) => ({
    today: new Date(),
    current: new Date(),

    get year()  { return this.current.getFullYear(); },
    get month() { return this.current.getMonth(); },

    get monthName() {
        return this.current.toLocaleString('default', { month: 'long', year: 'numeric' });
    },

    get days() {
        const first = new Date(this.year, this.month, 1);
        const last  = new Date(this.year, this.month + 1, 0);
        const blanks = Array(first.getDay()).fill(null);
        const days = [];
        for (let d = 1; d <= last.getDate(); d++) {
            const iso = `${this.year}-${String(this.month + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            days.push({ day: d, iso, event: eventDates[iso] ?? null });
        }
        return [...blanks, ...days];
    },

    prev() { this.current = new Date(this.year, this.month - 1, 1); },
    next() { this.current = new Date(this.year, this.month + 1, 1); },

    isToday(iso) {
        const t = this.today;
        return iso === `${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,'0')}-${String(t.getDate()).padStart(2,'0')}`;
    },

    navigate(event) {
        if (event?.url) window.location.href = baseUrl + '/' + event.url;
    },
}));

Alpine.data('imageCarousel', (urls) => ({
    images: urls,
    current: 0,
    init() {
        if (this.images.length > 1) {
            setInterval(() => {
                this.current = (this.current + 1) % this.images.length;
            }, 3000);
        }
    },
}));

Alpine.data('attendeeResults', (initial) => ({
    attended: initial.attended,
    absent:   initial.absent,
    draggingIndex: null,

    addPlayer(player) {
        this.absent = this.absent.filter(p => p.id !== player.id);
        this.attended.push(player);
    },
    removePlayer(player) {
        this.attended = this.attended.filter(p => p.id !== player.id);
        this.absent.push(player);
    },
    dragStart(index) {
        this.draggingIndex = index;
    },
    dragEnter(index) {
        if (this.draggingIndex === null || this.draggingIndex === index) return;
        const arr = [...this.attended];
        const [item] = arr.splice(this.draggingIndex, 1);
        arr.splice(index, 0, item);
        this.attended = arr;
        this.draggingIndex = index;
    },
    dragEnd() {
        this.draggingIndex = null;
    },
}));

window.Alpine = Alpine;
Alpine.start();
