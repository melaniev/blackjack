<?php
   
    // Create an array of face values
    // and an array of card values
    // then merge them together
    $cards = array_merge(array("J", "Q", "K", "A"), range(2,10)); // 13 cards
   
    // Shuffle the cards
    shuffle($cards);
   
    // Create an multidimentional array to hold the 4 suits
    $suits = array(
        'Heart' => array(),
        'Spade' => array(),
        'Diamond' => array(),
        'Club' => array()
        );
       
    // Add cards to their respective suits
    for($i = 0; $i < count($suits); $i++)
    {
        for($j = 0; $j < count($cards); $j++)
        {
            $suits['Heart'][$j] = $cards[$j]."<span style=color:#FF0000;>&hearts;</span>";
            $suits['Spade'][$j] = $cards[$j]."&spades;";
            $suits['Diamond'][$j] = $cards[$j]."<span style=color:#FF0000;>&diams;</span>";
            $suits['Club'][$j] = $cards[$j]."&clubs;";
        }
    }
   
    // Create a deck
    $deck = array();
   
    // Merge the suits into the empty deck array
    $deck = array_merge($deck, $suits);
               
    // Display the deck to the screen
/*
    echo "<p><b>Deck of cards:</b></p>";
    foreach($deck as $k1 => $v1)
    {
        // Display suit name
        echo "<p>&emsp;$k1's<br />&emsp;{<br />&emsp;&emsp;";
        $acc = 0;
       
        // Display card value
        foreach($v1 as $k2 => $v2)
        {
            echo "$v2&nbsp";
            $acc++;
           
            if ($acc == 4)
            {
                echo "<br />&emsp;&emsp;";
                $acc = 0;
            }
        }
        echo "<br />&emsp;}</p>"; 
*/
    }
?>
