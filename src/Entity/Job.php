<?php
namespace Werkint\Bundle\QueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableMethods;

/**
 * Действие над курсом
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 *
 * @ORM\Entity(repositoryClass="JobRepository")
 * @ORM\Table(name="werkint_queue_job"
 * , indexes={
 *     @ORM\Index(name="class_idx", columns={"class"}),
 *     @ORM\Index(name="date_idx", columns={"createdAt"}),
 *     @ORM\Index(name="active_idx", columns={"active", "createdAt"})
 *   }
 * )
 */
class Job
{
    use TimestampableMethods;

    public function __construct()
    {
        $this->setActive(true);
    }

    /**
     * @Serializer\Groups("=false && g('admin')")
     */
    protected $createdAt;
    /**
     * @Serializer\Exclude()
     */
    protected $updatedAt;
    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups("=false && g('admin')")
     */
    protected $processedAt;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     * @Serializer\Groups("=false && g('admin')")
     **/
    protected $class;
    /**
     * Данные для выполнения шаблона
     *
     * @var array
     * @ORM\Column(name="data",type="json_array")
     * @Serializer\Groups("=false && g('admin')")
     */
    protected $data;
    /**
     * Данные для выполнения шаблона
     *
     * @var boolean
     * @ORM\Column(name="active",type="boolean")
     * @Serializer\Groups("=false && g('admin')")
     */
    protected $active;

    /**
     * @param Job $job
     * @return boolean
     */
    public function isSameAs(Job $job)
    {
        return $this->getClass() === $job->getClass() && $this->getData() === $job->getData();
    }

    // -- Accessors ---------------------------------------

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime|null
     */
    public function getProcessedAt()
    {
        return $this->processedAt;
    }

    /**
     * @param \DateTime|null $processedAt
     * @return $this
     */
    public function setProcessedAt(\DateTime $processedAt = null)
    {
        $this->processedAt = $processedAt;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = (bool)$active;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }
}