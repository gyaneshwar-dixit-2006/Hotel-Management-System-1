<?php

class BookingDetailHandler extends BookingDetailDAO
{
    public function __construct()
    {
    }

    private $executionFeedback;

    public function getExecutionFeedback()
    {
        return $this->executionFeedback;
    }

    public function setExecutionFeedback($executionFeedback)
    {
        $this->executionFeedback = $executionFeedback;
    }

    public function getAllBookings()
    {
        $res = $this->fetchBooking();
        return is_array($res) ? $res : [];
    }

    public function getCustomerBookings(Customer $c)
    {
        if (!$c || !$c->getId()) {
            return [];
        }
        $res = $this->fetchBookingByCid($c->getId());
        return is_array($res) ? $res : [];
    }

    public function getPending()
    {
        $count = 0;
        foreach ($this->getAllBookings() as $v) {
            if (isset($v["status"]) && strtoupper($v["status"]) === 'PENDING') {
                $count++;
            }
        }
        return $count;
    }

    public function getConfirmed()
    {
        $count = 0;
        foreach ($this->getAllBookings() as $v) {
            if (isset($v["status"]) && (strtoupper($v["status"]) === 'CONFIRMED' || strtoupper($v["status"]) === 'CHECKED_IN')) {
                $count++;
            }
        }
        return $count;
    }

    public function getCancelled()
    {
        $count = 0;
        foreach ($this->getAllBookings() as $v) {
            if (isset($v["status"]) && strtoupper($v["status"]) === 'CANCELLED') {
                $count++;
            }
        }
        return $count;
    }

    public function confirmSelection($item)
    {
        if (!is_array($item)) {
            $item = [$item];
        }
        $success = 0;
        foreach ($item as $id) {
            if (is_numeric($id) && $this->updateConfirmed($id)) {
                $success++;
            }
        }
        if ($success > 0) {
            $this->setExecutionFeedback("Selected reservation(s) successfully marked as <b>CONFIRMED</b>.");
        } else {
            $this->setExecutionFeedback("No reservations were updated.");
        }
    }

    public function cancelSelection($item)
    {
        if (!is_array($item)) {
            $item = [$item];
        }
        $success = 0;
        foreach ($item as $id) {
            if (is_numeric($id) && $this->updateCancelled($id)) {
                $success++;
            }
        }
        if ($success > 0) {
            $this->setExecutionFeedback("Selected reservation(s) successfully marked as <b>CANCELLED</b>.");
        } else {
            $this->setExecutionFeedback("No reservations were updated.");
        }
    }

    public function updateBookingStatus($id, $status)
    {
        return $this->updateStatus($id, $status);
    }
}