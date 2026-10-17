const DELUXE_PER_NIGHT = 23135;
const DOUBLE_PER_NIGHT = 16657;
const SINGLE_PER_NIGHT = 12030;

const multiStepRsvnFormId = '#multiStepRsvnForm';
const multiStepRsvnformData = {
  cDate: function (dt) {
    let subject = new Date(dt);
    if (isNaN(subject.getTime())) subject = new Date();
    return [subject.getFullYear(), String(subject.getMonth() + 1).padStart(2, '0'), String(subject.getDate()).padStart(2, '0')].join('-');
  },
  d: function () {
    let rawTotal = ($('.totalTxt').first().text() || '0').replace(/,/g, '');
    let nights = parseInt($('.numNightsTxt').first().text()) || 1;
    return {
      cid: $('input[name="cid"]').val(),
      start: $('input[name="startDate"]').val(),
      end: $('input[name="endDate"]').val(),
      type: $('select[name="roomType"]').val(),
      requirement: $('select[name="roomRequirement"]').val(),
      adults: $('select[name="adults"]').val(),
      children: $('select[name="children"]').val(),
      requests: $('textarea[name="specialRequests"]').val(),
      bookedDate: new Date().toISOString().split('T')[0],
      numNights: nights,
      totalPrice: parseFloat(rawTotal) || (nights * 16657),
      readySubmit: $('#rsvnNextBtn').attr('readySubmit')
    };
  }
};

let currentTab = 0;
$(document).ready(function() {
  if ($('.rsvnTab').length > 0) {
    showTab(currentTab);
  }
});

function showTab (n) {
  let x = document.getElementsByClassName('rsvnTab');
  if (!x || x.length === 0) return;
  
  for (let i = 0; i < x.length; i++) {
    x[i].style.display = 'none';
  }
  x[n].style.display = 'block';

  if (n === 0) {
    document.getElementById('rsvnPrevBtn').style.display = 'none';
  } else {
    document.getElementById('rsvnPrevBtn').style.display = 'inline-block';
  }

  let rsvnNextBtn = $('#rsvnNextBtn');
  if (n === (x.length - 1)) {
    rsvnNextBtn.html('<i class="fa-solid fa-check mr-1"></i> Confirm & Book');
    rsvnNextBtn.attr('readySubmit', 'true');
    rsvnNextBtn.attr('onclick', 'submitMultiStepRsvn()');
  } else {
    rsvnNextBtn.html('Next <i class="fa-solid fa-arrow-right ml-1"></i>');
    rsvnNextBtn.attr('readySubmit', 'false');
    rsvnNextBtn.attr('onclick', 'rsvnNextPrev(1)');
  }
  fixStepIndicator(n);
}

function submitMultiStepRsvn () {
  let canSubmit = $('#rsvnNextBtn').attr('readySubmit');
  if (canSubmit !== 'true') {
    return false;
  }
  
  let d = multiStepRsvnformData.d();
  $('#rsvnNextBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> Processing...');

  $.ajax({
    url: 'app/process_reservation.php',
    type: 'post',
    data: d
  }).done(function (response) {
    try {
      let out = JSON.parse(response);
      if (out.success === 'true') {
        $(multiStepRsvnFormId).find('.alert').remove();
        $(multiStepRsvnFormId).prepend(out.response);
        $('#rsvnNextBtn').hide();
        $('#rsvnPrevBtn').hide();
        setTimeout(function() {
          location.reload();
        }, 2200);
      } else {
        $(multiStepRsvnFormId).prepend(out.response);
        $('#rsvnNextBtn').prop('disabled', false).text('Confirm & Book');
      }
    } catch (e) {
      $(multiStepRsvnFormId).prepend(response);
      $('#rsvnNextBtn').prop('disabled', false).text('Confirm & Book');
    }
  }).fail(function() {
    alert('Server communication error. Please try again.');
    $('#rsvnNextBtn').prop('disabled', false).text('Confirm & Book');
  });
}

function fixStepIndicator (n) {
  let x = document.getElementsByClassName('step');
  for (let i = 0; i < x.length; i++) {
    x[i].className = x[i].className.replace(' active', '');
  }
  if (x[n]) {
    x[n].className += ' active';
  }
}

function rsvnNextPrev (n) {
  let x = document.getElementsByClassName('rsvnTab');
  if (n === 1 && !validateRsvnForm()) return false;
  if (!x[currentTab]) return;

  x[currentTab].style.display = 'none';
  currentTab = currentTab + n;

  if (currentTab >= x.length) {
    submitMultiStepRsvn();
    return false;
  }
  showTab(currentTab);
}

function validateRsvnForm () {
  let tab = document.getElementsByClassName('rsvnTab');
  if (!tab || !tab[currentTab]) return true;

  let valid = true;
  let inputs = tab[currentTab].getElementsByTagName('input');
  for (let i = 0; i < inputs.length; i++) {
    if (inputs[i].hasAttribute('required')) {
      if (inputs[i].value === '') {
        inputs[i].classList.add('is-invalid');
        valid = false;
      } else {
        inputs[i].classList.remove('is-invalid');
      }
    }
  }

  let selects = tab[currentTab].getElementsByTagName('select');
  for (let i = 0; i < selects.length; i++) {
    if (selects[i].hasAttribute('required')) {
      if (selects[i].value === '') {
        selects[i].classList.add('is-invalid');
        valid = false;
      } else {
        selects[i].classList.remove('is-invalid');
      }
    }
  }

  // Validate dates: end date must be after start date
  if (currentTab === 0) {
    let s = new Date($('input[name="startDate"]').val());
    let e = new Date($('input[name="endDate"]').val());
    if (e <= s) {
      alert('Check-out date must be after check-in date.');
      return false;
    }
  }

  if (valid) {
    let steps = document.getElementsByClassName('step');
    if (steps[currentTab]) {
      steps[currentTab].classList.add('finish');
    }
    new ReservationCost(
      $('select[name="roomType"]').val(),
      $('input[name="startDate"]').val(),
      $('input[name="endDate"]').val()
    ).displayAll();
  }
  return valid;
}

class ReservationCost {
  constructor (roomType, startDate, endDate) {
    this.roomType = roomType || 'Double';
    this.startDate = new Date(startDate);
    this.endDate = new Date(endDate);
  }

  priceByRoomType () {
    if (this.roomType === 'Deluxe') return DELUXE_PER_NIGHT;
    if (this.roomType === 'Double') return DOUBLE_PER_NIGHT;
    if (this.roomType === 'Single') return SINGLE_PER_NIGHT;
    return 16657;
  }

  numNights () {
    let diff = this.endDate - this.startDate;
    let days = Math.round(diff / (1000 * 60 * 60 * 24));
    return isNaN(days) || days <= 0 ? 1 : days;
  }

  displayAll () {
    let nights = this.numNights();
    let pricePerNight = this.priceByRoomType();
    let total = nights * pricePerNight;

    $('.numNightsTxt').text(nights);
    $('.roomPricePerNightTxt').text(pricePerNight.toLocaleString());
    $('.roomPriceTxt').text(total.toLocaleString());
    $('.totalTxt').text(total.toLocaleString());

    let sStr = this.startDate.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    let eStr = this.endDate.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    $('.fromToTxt').text(sStr + ' to ' + eStr);
  }
}
