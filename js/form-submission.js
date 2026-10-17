const formIds = {
  register: '#registration-form',
  login: '#login-form',
  logout: '#sign-out-link, #admin-sign-out',
  reservation: '#reservation-form',
  updateProfile: '#update-profile-form'
};

const formData = {
  registration: function () {
    return {
      fullName: $('input[name="registrationFullName"]').val(),
      phoneNumber: $("input[name='registrationPhoneNumber']").val(),
      email: $("input[name='registrationEmail']").val(),
      password: $("input[name='registrationPassword']").val(),
      password2: $("input[name='registrationPassword2']").val(),
      submitBtn: 'updatebtn'
    };
  },
  login: function () {
    return {
      email: $('input[name="loginEmail"]').val(),
      password: $('input[name="loginPassword"]').val(),
      submitBtn: '1'
    };
  },
  updateProfile: function () {
    return {
      cid: $('input[name="customerId"]').val(),
      fullName: $('input[name="updateFullName"]').val(),
      phone: $("input[name='updatePhoneNumber']").val(),
      email: $("input[name='updateEmail']").val(),
      newPassword: $("input[name='updatePassword']").val(),
      submitBtn: 'updatebtn'
    };
  }
};

const registrationSubmit = function () {
  let registrationData = formData.registration();
  $.ajax({
    url: 'app/process_registration.php',
    type: 'post',
    data: registrationData
  }).done(function (response) {
    $('#registerAlert, #registration-form').find('.alert').remove();
    $('#registerAlert').prepend(response);
  }).fail(function() {
    alert('An error occurred during registration.');
  });
};

const loginSubmit = function () {
  let loginData = formData.login();
  $.ajax({
    url: 'app/process_login.php',
    type: 'post',
    data: loginData
  }).done(function (response) {
    try {
      let resp = JSON.parse(response);
      if (resp[0] === 1 || resp.status === 'success') {
        let isAdmin = (resp[1] === 'true' || resp.isAdmin === true);
        if (typeof UtilityFunctions !== 'undefined') {
          new UtilityFunctions().setCookie('is_admin', isAdmin ? 'true' : 'false');
        }
        if (isAdmin) {
          window.location.replace('admin.php');
        } else {
          window.location.replace('index.php');
        }
        return;
      }
    } catch (e) {
      // response was HTML alert
    }
    $('#loginAlertBox, ' + formIds.login).find('.alert').remove();
    $('#loginAlertBox').prepend(response);
  }).fail(function() {
    alert('Failed to connect to authentication server.');
  });
};

const clickSignOut = function () {
  $.ajax({
    url: 'app/process_logout.php',
    type: 'get'
  }).done(function () {
    if (typeof UtilityFunctions !== 'undefined') {
      new UtilityFunctions().eraseCookie('is_admin');
    }
    window.location.replace('index.php');
  }).fail(function() {
    window.location.replace('index.php');
  });
};

const updateProfileSubmit = function () {
  let updateData = formData.updateProfile();
  $.ajax({
    url: 'app/process_update_profile.php',
    type: 'post',
    data: updateData
  }).done(function (response) {
    $(formIds.updateProfile).find('.alert').remove();
    $(formIds.updateProfile).prepend(response);
    setTimeout(function() {
      location.reload();
    }, 1800);
  }).fail(function() {
    alert('Error updating profile.');
  });
};

$(document).ready(function () {
  $(formIds.register).submit(function (event) {
    registrationSubmit();
    event.preventDefault();
    return false;
  });

  $(formIds.login).submit(function (event) {
    loginSubmit();
    event.preventDefault();
    return false;
  });

  $(document).on('click', formIds.logout, function (event) {
    clickSignOut();
    event.preventDefault();
    return false;
  });

  $(formIds.updateProfile).submit(function (event) {
    updateProfileSubmit();
    event.preventDefault();
    return false;
  });
});
