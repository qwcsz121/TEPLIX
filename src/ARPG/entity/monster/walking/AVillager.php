<?php

namespace ARPG\entity\monster\walking;

use ARPG\entity\monster\WalkingMonster;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;

class AVillager extends WalkingMonster{
 const NETWORK_ID = 15;

 public $width = 0.72;
 public $height = 1.8;

 public function initEntity(){
  parent::initEntity();

  $this->setMaxDamage(8);
  $this->setDamage([3, 4, 5, 6]);
 }

 public function getName(){
  return "Villager";
 }
 
 public function attackEntity(Entity $player){
  if($this->attackDelay > 10 && $this->distanceSquared($player) < 1){
   $this->attackDelay = 0;

   $ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
   $player->attack($ev->getFinalDamage(), $ev);
  }
 }

 public function getDrops(){
  return [];
 }

}
