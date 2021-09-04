<?php

namespace ARPG\entity\monster\walking;

use ARPG\entity\monster\walkingMonster;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

class ALavaSlime extends WalkingMonster{
 const NETWORK_ID = 42;

 public $width = 1.2;
 public $height = 1.2;

 public function initEntity(){
  parent::initEntity();

  $this->fireProof = true;
  $this->setDamage([0, 3, 4, 6]);
 }

 public function getName(){
  return "LavaSlime";
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
