@extends('frontend.layouts.app'.config('theme_layout'))
@section('title', trans('labels.frontend.appointments.title').' | '. app_name() )

@push('after-styles')
<style>
    #calendar-wrapper {
        display: flex;
        flex-direction: column;
    }
    #calendar {
        display: flex;
        flex-direction: column;
    }
    #calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        font-weight: bold;
        margin-top: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ccc;
    }
    #calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        grid-template-rows: repeat(6, 1fr);
    }

    #calendar-grid > time {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 5px;
        border-radius: 50%;
        width: 2rem;
        height: 2rem;
        cursor: pointer;
    }

    #calendar-grid > time:not(.disabled) {
        font-weight: bold;
        color: #00c800;
    }

    #calendar-grid > time.selected{
        background-color: #007bff;
        color: white;
    }

    #navigation-wrapper {
        display: flex;
        justify-content: space-between;
        margin: 20px 0;
    }

    #navigation-wrapper button {
        width: 3rem;
        height: 3rem;
        border:1px solid gray;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: none;
    }

    #navigation-wrapper #previous-month{
        transform: rotate(90deg);
    }

    #navigation-wrapper #next-month{
         transform: rotate(270deg);
     }

    #navigation-wrapper h4 {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .category-btn:disabled {
        opacity: 1!important;
    }

    .loading-container {
        filter: blur(3px);
    }
</style>
<script>
    // Polyfill for Element.prototype.closest (for IE 9+)
    if (!Element.prototype.matches) { Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector; }
    if (!Element.prototype.closest) {
        Element.prototype.closest = function(s) {
            var el = this;
            do {
                if (Element.prototype.matches.call(el, s)) return el;
                el = el.parentElement || el.parentNode;
            } while (el !== null && el.nodeType === 1);
            return null;
        };
    }

    /**
     * @class CalendarPicker.
     * @description Provides a simple way to get a minimalistic calender in your DOM.
     * @author Mathias Picker - 29 July 2020.
     */
    function CalendarPicker(element, options) {
        // Core variables.
        this.date = new Date();
        this._formatDateToInit(this.date);

        this.availableDays = options.availableDays;

        this.day = this.date.getDay()
        this.month = this.date.getMonth();
        this.year = this.date.getFullYear();

        // Storing the todays date for practical reasons.
        this.today =this.date;

        // The calendars value should always be the current date.
        this.value = options.selected ?? this.date;

        // Ranges for the calendar (optional).
        this.min = options.min;
        this.max = options.max;
        this._formatDateToInit(this.min);
        this._formatDateToInit(this.max);

        // Element to insert calendar in.
        this.userElement = document.querySelector(element);

        // Setting current date as readable text.
        this._setDateText();

        // The elements used to build the calendar.
        this.calendarWrapper = document.createElement('div');
        this.calendarElement = document.createElement('div');
        this.calendarHeader = document.createElement('header');
        this.calendarHeaderTitle = document.createElement('h4');
        this.navigationWrapper = document.createElement('section');
        this.previousMonthArrow = document.createElement('button');
        this.nextMonthArrow = document.createElement('button');
        this.calendarGridDays = document.createElement('section');
        this.calendarGrid = document.createElement('section');
        this.calendarDayElementType = 'time';

        // Hard-coded list of all days.
        this.listOfAllDaysAsText = [
            'Mon',
            'Tue',
            'Wed',
            'Thu',
            'Fri',
            'Sat',
            'Sun'
        ];

        // Hard-coded list of all months.
        this.listOfAllMonthsAsText = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ];

        // Creating the calendar
        this.calendarWrapper.id = 'calendar-wrapper';
        this.calendarElement.id = 'calendar';
        this.calendarGridDays.id = 'calendar-days';
        this.calendarGrid.id = 'calendar-grid';
        this.navigationWrapper.id = 'navigation-wrapper';
        this.previousMonthArrow.id = 'previous-month';
        this.nextMonthArrow.id = 'next-month';

        this._insertHeaderIntoCalendarWrapper();
        this._insertNavigationButtons();
        this._insertCalendarGridDaysHeader();
        this._insertDaysIntoGrid();
        this._insertCalendarIntoWrapper();

        this.userElement?.appendChild(this.calendarWrapper);
    }


    /**
     * @param {Number} The month number, 0 based.
     * @param {Number} The year, not zero based, required to account for leap years.
     * @return {Array<Date>} List with date objects for each day of the month.
     * @author Juan Mendes - 30th October 2012.
     */
    CalendarPicker.prototype._getDaysInMonth = function (month, year) {
        if ((!month && month !== 0) || (!year && year !== 0)) return;

        var date = new Date(year, month, 1);
        var days = [];

        while (date.getMonth() === month) {
            days.push(new Date(date));
            date.setDate(date.getDate() + 1);
        }
        return days;
    }

    /**
     * @param {DateObject} date.
     * @description Sets the clock of a date to 00:00:00 to be consistent.
     */
    CalendarPicker.prototype._formatDateToInit = function (date) {
        if (!date) return;
        date.setHours(0, 0, 0, 0);
    }

    /**
     * @description Sets the current date as readable text in their own variables
     */
    CalendarPicker.prototype._setDateText = function () {
        // Setting current date as readable text.
        var dateData = this.date.toString().split(' ');
        this.dayAsText = dateData[0];
        this.monthAsText = dateData[1];
        this.dateAsText = dateData[2];
        this.yearAsText = dateData[3];
    }

    /**
     * @description Inserts the calendar into the wrapper and adds eventListeners for the calender-grid.
     */
    CalendarPicker.prototype._insertCalendarIntoWrapper = function () {
        this.calendarWrapper.appendChild(this.calendarElement);

        /**
         * @param {Event} event An event from an eventListener.
         */
        var handleSelectedElement = (event) => {
            if (event.target.nodeName.toLowerCase() === this.calendarDayElementType && !event.target.classList.contains('disabled')) {

                // Removes the 'selected' class from all elements that have it.
                Array.from(document.querySelectorAll('.selected')).forEach(element => element.classList.remove('selected'));

                // Adds the 'selected'-class to the selected date.
                event.target.classList.add('selected');

                this.value = event.target.value;

                // Fires the onValueChange function with the provided callback.
                this.onValueChange(this.callback);
            }
        }

        this.calendarGrid.addEventListener('click', handleSelectedElement, false);

        this.calendarGrid.addEventListener('keydown', (keyEvent) => {
            if (keyEvent.key !== 'Enter') return;

            handleSelectedElement(keyEvent);
        }, false);
    }

    /**
     * @description Adds the "main" calendar-header.
     */
    CalendarPicker.prototype._insertHeaderIntoCalendarWrapper = function () {
        this.calendarHeaderTitle.textContent = this.listOfAllMonthsAsText[this.month] + ' - ' + this.year;
        this.calendarHeader.appendChild(this.calendarHeaderTitle);
        this.calendarWrapper.appendChild(this.calendarHeader);
    }

    /**
     * @description Adds the "main" calendar-header.
     */
    CalendarPicker.prototype._insertHeaderIntoCalendarNavigation = function () {
        this.calendarHeaderTitle.textContent = this.listOfAllMonthsAsText[this.month] + ' - ' + this.year;
        this.calendarHeader.appendChild(this.calendarHeaderTitle);
        this.calendarWrapper.appendChild(this.calendarHeader);
    }

    /**
     * @description Inserts the calendar-grid header with all the weekdays.
     */
    CalendarPicker.prototype._insertCalendarGridDaysHeader = function () {
        this.listOfAllDaysAsText.forEach(day => {
            var dayElement = document.createElement('span');
            dayElement.textContent = day;
            this.calendarGridDays.appendChild(dayElement);
        })

        this.calendarElement.appendChild(this.calendarGridDays);
    }

    /**
     * @description Adds the "Previous" and "Next" arrows on the side-navigation.
     * Also inits the click-events used to navigating.
     */
    CalendarPicker.prototype._insertNavigationButtons = function () {
        // Ugly long string, but at least the svg is pretty.
        var arrowSvg = '<svg enable-background="new 0 0 386.257 386.257" viewBox="0 0 386.257 386.257" xmlns="http://www.w3.org/2000/svg"><path d="m0 96.879 193.129 192.5 193.128-192.5z"/></svg>';

        this.previousMonthArrow.innerHTML = arrowSvg;
        this.nextMonthArrow.innerHTML = arrowSvg;

        this.previousMonthArrow.setAttribute('aria-label', 'Go to previous month');
        this.nextMonthArrow.setAttribute('aria-label', 'Go to next month');

        this.navigationWrapper.appendChild(this.previousMonthArrow);
        this.calendarHeaderTitle.textContent = this.listOfAllMonthsAsText[this.month] + ' - ' + this.year;
        this.navigationWrapper.appendChild(this.calendarHeaderTitle);
        this.navigationWrapper.appendChild(this.nextMonthArrow);

        // Cannot use arrow-functions for IE support :(
        var that = this;
        this.navigationWrapper.addEventListener('click', function (clickEvent) {
            if (clickEvent.target.closest('#' + that.previousMonthArrow.id)) {
                if (that.month === 0) {
                    that.month = 11;
                    that.year -= 1;
                } else {
                    that.month -= 1;
                }
                that._updateCalendar();
            }

            if (clickEvent.target.closest('#' + that.nextMonthArrow.id)) {
                if (that.month === 11) {
                    that.month = 0;
                    that.year += 1;
                } else {
                    that.month += 1;
                }
                that._updateCalendar();
            }
        }, false)

        that.calendarElement.appendChild(that.navigationWrapper);
    }

    /**
     * @description Adds all the days for current month into the calendar-grid.
     * Takes into account which day the month starts on, so that "empty/placeholder" days can be added
     * in case the month for example starts on a Thursday.
     * Also disables the days that are not within the provided.
     */
    CalendarPicker.prototype._insertDaysIntoGrid = function () {
        this.calendarGrid.innerHTML = '';

        var arrayOfDays = this._getDaysInMonth(this.month, this.year);
        var firstDayOfMonth = arrayOfDays[0].getDay();

        // Converting Sunday (0 when using getDay()) to 7 to make it easier to work with.
        firstDayOfMonth = firstDayOfMonth === 0 ? 7 : firstDayOfMonth;

        if (1 < firstDayOfMonth) {
            arrayOfDays = Array(firstDayOfMonth - 1).fill(false, 0).concat(arrayOfDays);
        }

        arrayOfDays.forEach(date => {
            var currentDate = date;
            var dateElement = document.createElement(date ? this.calendarDayElementType : 'span');
            var Date = date.toString().split(' ')[2];

            var dateIsTheCurrentValue = this.value.toString() === date.toString();
            if (dateIsTheCurrentValue) this.activeDateElement = dateElement;

            let isTeacherAvailable = this.availableDays.includes(new window.Date(date.toString()).getDay());

            var dateIsBetweenAllowedRange = (this.min || this.max) && (date.toString() !== this.today.toString() && (date < this.min || date > this.max))

            if (dateIsBetweenAllowedRange || !isTeacherAvailable) {
                dateElement.classList.add('disabled');
            } else {
                dateElement.tabIndex = 0;
                dateElement.value = date;
            }

            dateElement.textContent = date ? Date : '';
            this.calendarGrid.appendChild(dateElement);
        })

        this.calendarElement.appendChild(this.calendarGrid);
        this.activeDateElement.classList.add('selected');
    }

    /**
     * @description Updates the core-values for the calendar based on the new month and year
     * given by the navigation. Also updates the UI with the new values.
     */
    CalendarPicker.prototype._updateCalendar = function () {
        this.date = new Date(this.year, this.month);

        this._setDateText();

        this.day = this.date.getDay();
        this.month = this.date.getMonth();
        this.year = this.date.getFullYear();

        // Cannot use arrow-functions for IE support :(
        var that = this;
        window.requestAnimationFrame(function () {
            that.calendarHeaderTitle.textContent = that.listOfAllMonthsAsText[that.month] + ' - ' + that.year;
            that._insertDaysIntoGrid();
        })
    }

    /**
     * @param {Function} callback
     * @description A "listener" that lets the user do something everytime the value changes.
     */
    CalendarPicker.prototype.onValueChange = function (callback) {
        if (this.callback) return this.callback(this.value);
        this.callback = callback;
    }
</script>
@endpush

@section('content')

    <!-- Start of breadcrumb section
        ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold">
                        <span>@lang('labels.frontend.appointments.title')</span>
                    </h2>
                </div>
            </div>
        </div>
    </section>
    <!-- End of breadcrumb section
        ============================================= -->
    <section>
        <div class="container">
            <livewire:frontend.appointments.book-an-appointment/>
        </div>
    </section>

@endsection

@push('after-scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            Livewire.hook('element.initialized', (el, component) => {
                component.$wire.set('data.timezone_offset', (new Date()).getTimezoneOffset());
            });

            Livewire.hook('message.processed', (el, component) => {
                let selectDate = new Date();
                if (component.$wire.get('data.date')) {
                    selectDate = new Date(component.$wire.get('data.date'));
                    selectDate.setHours(0, 0, 0, 0);
                } else {
                    selectDate.setHours(0, 0, 0, 0);
                    component.$wire.set('data.date', selectDate.getTime());
                }

                let availableDays = component.$wire.get('availableDays');

                setupDatePicker(selectDate, availableDays);
            });
        });

        function setupDatePicker(selectedDate = null, availableDays = []) {
            const nextYear = new Date().getFullYear() + 1;
            const myCalender = new CalendarPicker('#myCalendarWrapper', {
                min: new Date(),
                max: new Date(nextYear, 10),
                selected: selectedDate,
                availableDays: availableDays,
            });

            myCalender.onValueChange((currentValue) => {
                Livewire.emit('dateUpdated', currentValue.getTime())
            });
        }

    </script>
@endpush