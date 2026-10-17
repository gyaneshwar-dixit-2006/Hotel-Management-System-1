<?php

class BookingDetailDAO
{
    public function __construct()
    {
    }

    // use this in admin.php
    public function fetchBooking()
    {
        $sql = 'SELECT
          t1.id,
          t1.cid,
          t1.status,
          t1.notes,
          t2.start,
          t2.end,
          t2.type,
          t2.requirement,
          t2.adults,
          t2.children,
          t2.requests,
          t2.timestamp,
          t2.hash,
          t3.fullname,
          t3.email,
          t3.phone,
          COALESCE(t4.nights, 1) as nights,
          COALESCE(t4.total_price, 0) as total_price,
          COALESCE(t4.booked_date, DATE(t2.timestamp)) as booked_date
        FROM booking AS t1 
        LEFT JOIN reservation AS t2 ON t1.id = t2.id
        LEFT JOIN customer AS t3 ON t1.cid = t3.cid
        LEFT JOIN pricing AS t4 ON t1.id = t4.booking_id
        ORDER BY t1.id DESC;';
        $stmt = DB::getInstance()->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($res) ? $res : [];
    }

    // use in index.php
    public function fetchBookingByCid($cid)
    {
        $sql = 'SELECT
          t1.id,
          t1.cid,
          t1.status,
          t1.notes,
          t2.start,
          t2.end,
          t2.type,
          t2.requirement,
          t2.adults,
          t2.children,
          t2.requests,
          t2.timestamp,
          t2.hash,
          COALESCE(t4.nights, 1) as nights,
          COALESCE(t4.total_price, 0) as total_price,
          COALESCE(t4.booked_date, DATE(t2.timestamp)) as booked_date
        FROM booking AS t1 
        LEFT JOIN reservation AS t2 ON t1.id = t2.id
        LEFT JOIN pricing AS t4 ON t1.id = t4.booking_id
        WHERE t1.cid = ?
        ORDER BY t1.id DESC;';
        $stmt = DB::getInstance()->prepare($sql);
        $stmt->execute([$cid]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($res) ? $res : [];
    }

    public function updateStatus($id, $status)
    {
        $sql = 'UPDATE `booking` SET `status` = :status WHERE id = :id;';
        $stmt = DB::getInstance()->prepare($sql);
        return $stmt->execute(["id" => $id, "status" => strtoupper($status)]);
    }

    public function updateConfirmed($i)
    {
        return $this->updateStatus($i, "CONFIRMED");
    }

    public function updateCancelled($i)
    {
        return $this->updateStatus($i, "CANCELLED");
    }

    public function getTotalRevenue()
    {
        $sql = 'SELECT SUM(total_price) FROM pricing';
        $stmt = DB::getInstance()->prepare($sql);
        $stmt->execute();
        return (double) $stmt->fetchColumn();
    }
}
