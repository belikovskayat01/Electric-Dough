<?php

function getUserTotalOrdersCount($conn, $user_id) {
    $total_orders = 0;
    
    $booking_stmt = $conn->prepare("SELECT COUNT(*) as count FROM booking WHERE ID_user = ? AND Status = 'confirmed'");
    $booking_stmt->bind_param("i", $user_id);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $booking_row = $booking_result->fetch_assoc();
    $total_orders += $booking_row['count'];
    $booking_stmt->close();
    
    $preorder_stmt = $conn->prepare("SELECT COUNT(*) as count FROM pre_orders WHERE ID_user = ? AND Status IN ('confirmed', 'completed')");
    $preorder_stmt->bind_param("i", $user_id);
    $preorder_stmt->execute();
    $preorder_result = $preorder_stmt->get_result();
    $preorder_row = $preorder_result->fetch_assoc();
    $total_orders += $preorder_row['count'];
    $preorder_stmt->close();
    
    return $total_orders;
}

function calculatePointsFromOrders($orders_count) {
    if ($orders_count < 5) {
        return $orders_count * 15 + 100;
    } elseif ($orders_count < 15) {
        return $orders_count * 20 + 200;
    } else {
        return $orders_count * 25 + 500;
    }
}

function calculateDotPosition($points_earned, $level_class) {
    switch ($level_class) {
        case 'novice':
            if ($points_earned >= 250) {
                return 33;
            }
            $percent = ($points_earned / 250) * 33;
            return min(33, max(0, $percent));
            
        case 'star':
            if ($points_earned >= 1500) {
                return 100;
            }
            if ($points_earned <= 250) {
                return 33;
            }
            $progress = ($points_earned - 250) / (1500 - 250);
            $percent = 33 + ($progress * 67);
            return min(100, max(33, $percent));
            
        case 'legend':
            return 100;
            
        default:
            return 0;
    }
}

function calculateLoyaltyData($orders_count, $points_earned = null, $points_spent = null) {
    if ($orders_count == 0) {
        return getNewUserLoyaltyData();
    }
    
    if ($points_earned === null) {
        $points_earned = calculatePointsFromOrders($orders_count);
    }
    
    if ($points_spent === null) {
        $points_spent = rand(0, floor($points_earned * 0.3));
    }

    $loyalty_data = [
        'level' => 'НОВИЧОК',
        'level_class' => 'novice',
        'orders_count' => $orders_count,
        'points_earned' => $points_earned,
        'points_spent' => $points_spent,
        'points_to_next' => 250,
        'discount' => 0,
        'progress_width' => 0,
        'dot_position' => 0
    ];
    
    if ($orders_count >= 20 && $points_earned >= 1500) {
        $loyalty_data['level'] = 'ЛЕГЕНДА';
        $loyalty_data['level_class'] = 'legend';
        $loyalty_data['points_to_next'] = 0;
        $loyalty_data['discount'] = 10;
        $loyalty_data['progress_width'] = 100;
        $loyalty_data['dot_position'] = 100;
    } 
    elseif ($orders_count >= 10 && $points_earned >= 250) {
        $loyalty_data['level'] = 'ЗВЕЗДА';
        $loyalty_data['level_class'] = 'star';
        $loyalty_data['points_to_next'] = max(0, 1500 - $points_earned);
        $loyalty_data['discount'] = 5;
        $progress_percent = min(100, max(0, (($points_earned - 250) / (1500 - 250)) * 100));
        $loyalty_data['progress_width'] = $progress_percent;
        $loyalty_data['dot_position'] = calculateDotPosition($points_earned, 'star');
    } 
    else {
        $loyalty_data['level'] = 'НОВИЧОК';
        $loyalty_data['level_class'] = 'novice';
        $loyalty_data['points_to_next'] = max(0, 250 - $points_earned);
        $loyalty_data['discount'] = 0;
        $progress_percent = min(100, ($points_earned / 250) * 100);
        $loyalty_data['progress_width'] = $progress_percent;
        $loyalty_data['dot_position'] = 0;
    }
    
    return $loyalty_data;
}

function getNewUserLoyaltyData() {
    return [
        'level' => 'НОВИЧОК',
        'level_class' => 'novice',
        'orders_count' => 0,
        'points_earned' => 0,
        'points_spent' => 0,
        'points_to_next' => 250,
        'discount' => 0,
        'progress_width' => 0,
        'dot_position' => 0
    ];
}
?>