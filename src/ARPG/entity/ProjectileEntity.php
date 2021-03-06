<?php

namespace ARPG\entity;

use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\entity\Entity;
use pocketmine\entity\Projectile;
use pocketmine\math\Vector3;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\Potion;
use pocketmine\level\MovingObjectPosition;

abstract class ProjectileEntity extends Projectile{
 
 public $movingObjectPosition = null;
 
 public function saveNBT(){}
 
 public function onUpdate($currentTick){
  if($this->closed){
   return false;
  }


  $tickDiff = $currentTick - $this->lastUpdate;
  if($tickDiff <= 0 and !$this->justCreated){
   return true;
  }
  $this->lastUpdate = $currentTick;

  $hasUpdate = $this->entityBaseTick($tickDiff);

  if($this->isAlive()){

   $movingObjectPosition = null;

   if(!$this->isCollided){
    $this->motionY -= $this->gravity;
   }

   $moveVector = new Vector3($this->x + $this->motionX, $this->y + $this->motionY, $this->z + $this->motionZ);

   $list = $this->getLevel()->getCollidingEntities($this->boundingBox->addCoord($this->motionX, $this->motionY, $this->motionZ)->expand(1, 1, 1), $this);

   $nearDistance = PHP_INT_MAX;
   $nearEntity = null;

   foreach($list as $entity){
    if(/*!$entity->canCollideWith($this) or */
    ($entity === $this->shootingEntity and $this->ticksLived < 5)
    ){
     continue;
    }

    $axisalignedbb = $entity->boundingBox->grow(0.3, 0.3, 0.3);
    $ob = $axisalignedbb->calculateIntercept($this, $moveVector);

    if($ob === null){
     continue;
    }

    $distance = $this->distanceSquared($ob->hitVector);

    if($distance < $nearDistance){
     $nearDistance = $distance;
     $nearEntity = $entity;
    }
   }

   if($nearEntity !== null){
    $movingObjectPosition = MovingObjectPosition::fromEntity($nearEntity);
    $this->movingObjectPosition = $movingObjectPosition;
   }

   if($movingObjectPosition !== null){
    if($movingObjectPosition->entityHit !== null){

     $this->server->getPluginManager()->callEvent(new ProjectileHitEvent($this));

     $motion = sqrt($this->motionX ** 2 + $this->motionY ** 2 + $this->motionZ ** 2);
     $damage = ceil($motion * $this->damage);

     if($this instanceof Arrow and $this->isCritical()){
      $damage += mt_rand(0, (int) ($damage / 2) + 1);
     }
     
     $results = $this->attackOccurred($movingObjectPosition->entityHit, $damage);
     
     if($results === true){
      if($this instanceof Arrow and $this->getPotionId() != 0){
       foreach(Potion::getEffectsById($this->getPotionId() - 1) as $effect){
        $movingObjectPosition->entityHit->addEffect($effect->setDuration($effect->getDuration() / 8));
       }
      }
     }
     
     $this->hadCollision = true;

     if($this->fireTicks > 0){
      $ev = new EntityCombustByEntityEvent($this, $movingObjectPosition->entityHit, 5);
      $this->server->getPluginManager()->callEvent($ev);
      if(!$ev->isCancelled()){
       $movingObjectPosition->entityHit->setOnFire($ev->getDuration());
      }
     }

     $this->kill();
     return true;
    }
   }

   $this->move($this->motionX, $this->motionY, $this->motionZ);

   if($this->isCollided and !$this->hadCollision){
    $this->hadCollision = true;

    $this->motionX = 0;
    $this->motionY = 0;
    $this->motionZ = 0;

    $this->server->getPluginManager()->callEvent(new ProjectileHitEvent($this));
   }elseif(!$this->isCollided and $this->hadCollision){
    $this->hadCollision = false;
   }

   if(!$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001){
    $f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
    $this->yaw = (atan2($this->motionX, $this->motionZ) * 180 / M_PI);
    $this->pitch = (atan2($this->motionY, $f) * 180 / M_PI);
    $hasUpdate = true;
   }

   $this->updateMovement();

  }

  return $hasUpdate;
 }

 public function attackOccurred($target, $damage){
  
  if($target === null) return false;
  
  if($this->shootingEntity === null){
   $ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
  }else{
   $ev = new EntityDamageByChildEntityEvent($this->shootingEntity, $this, $target, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
  }
  
  if($target->attack($ev->getFinalDamage(), $ev) === true){
   $ev->useArmors();
   return true;
  }
  
  return false;
 }
 
}





























