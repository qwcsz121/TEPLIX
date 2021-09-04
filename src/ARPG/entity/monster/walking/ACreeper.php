<?php

namespace ARPG\entity\monster\walking;

use ARPG\entity\monster\WalkingMonster;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\sound\ExplodeSound;
use pocketmine\level\particle\HugeExplodeParticle;

class ACreeper extends WalkingMonster{
 const NETWORK_ID = 33;

 public $width = 0.72;
 public $height = 0.9;

 public function initEntity(){
  parent::initEntity();

  $this->setMaxDamage(8);
  $this->setDamage([3, 4, 5, 6]);
 }

 public function getName(){
  return "Creeper";
 }
 
 public function attackEntity(Entity $player){
  if($this->attackDelay > 20 && $this->distanceSquared($player) < 1){
   $this->attackDelay = 0;
   
   for($number = 1; $number <= 27; $number ++){
     
    $this->getLevel()->addParticle(new HugeExplodeParticle($this->add(
     mt_rand(-2,2) + mt_rand(-100, 100) / 500,
     mt_rand(-2,2) + mt_rand(-100, 100) / 500,
     mt_rand(-2,2) + mt_rand(-100, 100) / 500)
    ));
   }  
    
   foreach($this->level->getPlayers() as $player){
      
    if(($distance = $this->distance($player)) <= 3){
     
     $ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, ($this->arpg["攻击"]/3)*$distance);
       
     if($player->attack($ev->getFinalDamage(), $ev) === true) $ev->useArmors();
    }
   }
   
   $this->level->addSound(new ExplodeSound($this), $this->getViewers());
   $this->close();
  }
 }

 public function getDrops(){
  return [];
 }

}





















