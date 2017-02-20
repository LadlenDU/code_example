SELECT
  SUM(
      (`u`.`winnerID` = `f`.`winnerID`)
      * (
        10
        + (`u`.`roundID` = `f`.`roundID`) * 5
        + (`u`.`minuteID` = `f`.`minuteID`) * 5
        + (`u`.`methodID` = `f`.`methodID`) * 5
        + (`u`.`roundID` = `f`.`roundID` AND `u`.`minuteID` = `f`.`minuteID` AND `u`.`methodID` = `f`.`methodID`) * 5
      )
  )            AS `points`,
  `u`.`userID` AS `userID`
FROM
  `POOLS` `p`, `USERPICKS` `u`, `FIGHTS` `f`
WHERE
  `u`.status != 'PENDING'
  AND
  `p`.`status` = 'COMPLETE'
  AND
  `p`.`poolID` = '< pool_id >'
  AND
  `p`.`poolID` = `u`.`poolID`
  AND
  `f`.`poolID` = `u`.`poolID`
  AND
  `f`.`fightID` = `u`.`fightID`
GROUP BY
  `p`.`poolID`, `u`.`userID`