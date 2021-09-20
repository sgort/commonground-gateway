<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An possible attribute on an Entity
 *
 * @category Entity
 *
 * @ApiResource(
 *  normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *  denormalizationContext={"groups"={"write"}, "enable_max_depth"=true},
 *  itemOperations={
 *      "get",
 *      "put",
 *      "delete"
 *  },
 *  collectionOperations={
 *      "get",
 *      "post"
 *  })
 * @ORM\Entity(repositoryClass="App\Repository\AttributeRepository")
 * @Gedmo\Loggable(logEntryClass="Conduction\CommonGroundBundle\Entity\ChangeLog")
 *
 * @ApiFilter(BooleanFilter::class)
 * @ApiFilter(OrderFilter::class)
 * @ApiFilter(DateFilter::class, strategy=DateFilter::EXCLUDE_NULL)
 * @ApiFilter(SearchFilter::class)
 */
class Attribute
{
    /**
     * @var UuidInterface The UUID identifier of this object
     *
     * @example e2984465-190a-4562-829e-a8cca81aa35d
     *
     * @Groups({"read"})
     * @Assert\Uuid
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var string The name of the property as used in api calls
     *
     * @example my_property
     *
     * @Gedmo\Versioned
     * @Assert\Length(
     *     max = 255
     * )
     * @Assert\NotNull
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /* @todo slugify to key */

    /**
     * @var string The type of this property
     *
     * @example string
     *
     * @Assert\NotBlank
     * @Assert\Length(max = 255)
     * @Assert\Choice({"string", "integer", "boolean", "number", "datetime", "object"})
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @var string The swagger type of the property as used in api calls
     *
     * @example string
     *
     * @Assert\Length(max = 255)
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $format;

    /**
     * @var bool True if this attribute expects an array of the given type.
     *
     * @example true
     *
     * @Assert\Type("bool")
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean")
     */
    private bool $multiple = false;

    /**
     * @Groups({"write"})
     * @ORM\ManyToOne(targetEntity=Entity::class, inversedBy="attributes")
     * @MaxDepth(1)
     */
    private Entity $entity;

    /**
     * @Groups({"write"})
     * @ORM\OneToMany(targetEntity=Value::class, mappedBy="attribute", cascade={"remove"}, fetch="EXTRA_LAZY")
     * @MaxDepth(1)
     */
    private Collection $attributeValues;

    /**
     * @Groups({"read","write"})
     * @ORM\ManyToOne(targetEntity=Entity::class, inversedBy="usedIn", fetch="EAGER")
     * @ORM\JoinColumn(nullable=true)
     * @MaxDepth(1)
     */
    private ?Entity $object = null;

    /**
     * @var string *Can only be used in combination with type integer* Specifies a number where the value should be a multiple of, e.g. a multiple of 2 would validate 2,4 and 6 but would prevent 5
     *
     * @example 2
     *
     * @Assert\Type("integer")
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $multipleOf;

    /**
     * @var string *Can only be used in combination with type integer* The maximum allowed value
     *
     * @example 2
     *
     * @Assert\Type("integer")
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maximum;

    /**
     * @var string *Can only be used in combination with type integer* Defines if the maximum is exclusive, e.g. a exclusive maximum of 5 would invalidate 5 but validate 4
     *
     * @example true
     *
     * @Assert\Type("bool")
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $exclusiveMaximum;

    /**
     * @var string *Can only be used in combination with type integer* The minimum allowed value
     *
     * @example 2
     *
     * @Assert\Type("integer")
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minimum;

    /**
     * @var string *Can only be used in combination with type integer* Defines if the minimum is exclusive, e.g. a exclusive minimum of 5 would invalidate 5 but validate 6
     *
     * @example true
     *
     *
     * @Assert\Type("bool")
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $exclusiveMinimum;

    /**
     * @var string The maximum amount of characters in the value
     *
     * @example 2
     *
     * @Assert\Type("integer")
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxLength;

    /**
     * @var int The minimal amount of characters in the value
     *
     * @example 2
     *
     * @Assert\Type("integer")
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minLength;

    /**
     * @var string *Can only be used in combination with type array* The maximum array length
     *
     * @example 2
     *
     * @Assert\Type("integer")
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxItems;

    /**
     * @var string *Can only be used in combination with type array* The minimum array length
     *
     * @example 2
     *
     * @Assert\Type("integer")
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minItems;

    /**
     * @var bool *Can only be used in combination with type array* Define whether or not values in an array should be unique
     *
     * @example false
     *
     * @Assert\Type("bool")
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $uniqueItems;

    /**
     * @var string *Can only be used in combination with type object* The maximum amount of properties an object should contain
     *
     * @example 2
     *
     * @Assert\Type("integer")
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxProperties;

    /**
     * @var int *Can only be used in combination with type object* The minimum amount of properties an object should contain
     *
     * @example 2
     *
     * @Assert\Type("integer")
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minProperties;

    /**
     * @var string If the attribute targerts an object that object might have an inversedBy field allowing an two way connection
     *
     * @example property
     *
     * @Assert\Type("string")
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", nullable=true)
     */
    private $inversedBy;

    /**
     * @var bool Only whether or not this property is required
     *
     * @example false
     *
     * @Assert\Type("bool")
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $required;

    /**
     * @var array conditional requiremends for field
     *
     * @example false
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $requiredIf = [];


    /**
     * @var array conditional requiremends for field
     *
     * @example false
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $forbidenIf = [];

    /**
     * @var array An array of possible values, input is limited to this array]
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $enum = [];

    /**
     * @var array *mutually exclusive with using type* An array of possible types that an property should confirm to]
     *
     * @ORM\Column(type="array", nullable=true)
     */
    private $allOf = [];

    /**
     * @var array *mutually exclusive with using type* An array of possible types that an property might confirm to]
     *
     *
     * @ORM\Column(type="array", nullable=true)
     */
    private $anyOf = [];

    /**
     * @var array *mutually exclusive with using type* An array of possible types that an property must confirm to]
     *
     *
     * @ORM\Column(type="array", nullable=true)
     */
    private $oneOf = [];

    /**
     * @var string An description of the value asked, supports markdown syntax as described by [CommonMark 0.27.](https://spec.commonmark.org/0.27/)
     *
     * @example My value
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string An default value for this value that will be used if a user doesn't supply a value
     *
     * @example My value
     *
     * @Assert\Length(max = 255)
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $defaultValue;

    /**
     * @var bool Whether or not this property can be left empty
     *
     * @example false
     *
     * @Assert\Type("bool")
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $nullable;

    /**
     * @var bool Whether or not this property must be unique
     *
     * @example false
     *
     * @Assert\Type("bool")
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $mustBeUnique;

    /**
     * @var bool Whether or not this property is read only
     *
     * @example false
     *
     * @Assert\Type("bool")
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $readOnly;

    /**
     * @var bool Whether or not this property is write only
     *
     * @example false
     *
     * @Assert\Type("bool")
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $writeOnly;

    /**
     * @var string An example of the value that should be supplied
     *
     * @example My value
     *
     * @Assert\Length(max = 255)
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $example;

    /**
     * @var bool Whether or not this property has been deprecated and wil be removed in the future
     *
     * @example false
     *
     * @Assert\Type("bool")
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deprecated;

    /**
     * @var string The minimal date for value, either a date, datetime or duration (ISO_8601)
     *
     * @example 2019-09-16T14:26:51+00:00
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $minDate;

    /**
     * @var string The maximum date for value, either a date, datetime or duration (ISO_8601)
     *
     * @example 2019-09-16T14:26:51+00:00
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $maxDate;

    /**
     * @var array This convieniance property alows us to get and set our validations as an array instead of loose objects
     */
    private $validations;

    /**
     *
     * Setting this property to true wil force the property to be saved in the gateway endpoint (default behafure is saving in the EAV)
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $persistToGateway = false;

    /**
     *
     * Whether or not this property is searchable
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $searchable = false;

    /**
     *
     * Whether or not this property kan be used to create new entities (versus when it can only be used to link exsisting entities)
     *
     * @ORM\Column(type="boolean", nullable=true, name="allow_cascade")
     */
    private $cascade = false;

    /**
     * @var Datetime The moment this request was created
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var Datetime The moment this request last Modified
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateModified;

    public function __construct()
    {
        $this->attributeValues = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMultiple(): ?bool
    {
        return $this->multiple;
    }

    public function setMultiple(?bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function setEntity(?Entity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return Collection|Value[]
     */
    public function getAttributeValues(): Collection
    {
        return $this->attributeValues;
    }

    public function addAttributeValue(Value $attributeValue): self
    {
        if (!$this->attributeValues->contains($attributeValue)) {
            $this->attributeValues[] = $attributeValue;
            $attributeValue->setAttribute($this);
        }

        return $this;
    }

    public function removeAttributeValue(Value $attributeValue): self
    {
        if ($this->attributeValues->removeElement($attributeValue)) {
            // set the owning side to null (unless already changed)
            if ($attributeValue->getAttribute() === $this) {
                $attributeValue->setAttribute(null);
            }
        }

        return $this;
    }

    public function getObject(): ?Entity
    {
        return $this->object;
    }

    public function setObject(?Entity $object): self
    {
        $this->type = 'object';
        $this->object = $object;

        return $this;
    }

    public function getMultipleOf(): ?int
    {
        return $this->multipleOf;
    }

    public function setMultipleOf(?int $multipleOf): self
    {
        $this->multipleOf = $multipleOf;

        return $this;
    }

    public function getMaximum(): ?int
    {
        return $this->maximum;
    }

    public function setMaximum(?int $maximum): self
    {
        $this->maximum = $maximum;

        return $this;
    }

    public function getExclusiveMaximum(): ?bool
    {
        return $this->exclusiveMaximum;
    }

    public function setExclusiveMaximum(?bool $exclusiveMaximum): self
    {
        $this->exclusiveMaximum = $exclusiveMaximum;

        return $this;
    }

    public function getMinimum(): ?int
    {
        return $this->minimum;
    }

    public function setMinimum(?int $minimum): self
    {
        $this->minimum = $minimum;

        return $this;
    }

    public function getExclusiveMinimum(): ?bool
    {
        return $this->exclusiveMinimum;
    }

    public function setExclusiveMinimum(?bool $exclusiveMinimum): self
    {
        $this->exclusiveMinimum = $exclusiveMinimum;

        return $this;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function setMaxLength(?int $maxLength): self
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    public function setMinLength(?int $minLength): self
    {
        $this->minLength = $minLength;

        return $this;
    }

    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    public function setMaxItems(?int $maxItems): self
    {
        $this->maxItems = $maxItems;

        return $this;
    }

    public function getMinItems(): ?int
    {
        return $this->minItems;
    }

    public function setMinItems(int $minItems): self
    {
        $this->minItems = $minItems;

        return $this;
    }

    public function getUniqueItems(): ?bool
    {
        return $this->uniqueItems;
    }

    public function setUniqueItems(?bool $uniqueItems): self
    {
        $this->uniqueItems = $uniqueItems;

        return $this;
    }

    public function getMaxProperties(): ?int
    {
        return $this->maxProperties;
    }

    public function setMaxProperties(?int $maxProperties): self
    {
        $this->maxProperties = $maxProperties;

        return $this;
    }

    public function getMinProperties(): ?int
    {
        return $this->minProperties;
    }

    public function setMinProperties(?int $minProperties): self
    {
        $this->minProperties = $minProperties;

        return $this;
    }

    public function getRequired(): ?bool
    {
        return $this->required;
    }

    public function setRequired(?bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function getRequiredIf(): ?array
    {
        return $this->requiredIf;
    }

    public function setRequiredIf(?array $requiredIf): self
    {
        $this->requiredIf = $requiredIf;

        return $this;
    }


    public function getForbidenIf(): ?array
    {
        return $this->forbidenIf;
    }

    public function setForbidenIf(?array $forbidenIf): self
    {
        $this->forbidenIf = $forbidenIf;

        return $this;
    }

    public function getEnum(): ?array
    {
        return $this->enum;
    }

    public function setEnum(?array $enum): self
    {
        $this->enum = $enum;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        // If the attribute type is changes away from an object we need to drop the object
        if($type != 'object' and $this->object ) {
            unset($this->object);
        }

        return $this;
    }

    public function getAllOf(): ?array
    {
        return $this->allOf;
    }

    public function setAllOf(?array $allOf): self
    {
        $this->allOf = $allOf;

        return $this;
    }

    public function getAnyOf(): ?array
    {
        return $this->anyOf;
    }

    public function setAnyOf(?array $anyOf): self
    {
        $this->anyOf = $anyOf;

        return $this;
    }

    public function getOneOf(): ?array
    {
        return $this->oneOf;
    }

    public function setOneOf(?array $oneOf): self
    {
        $this->oneOf = $oneOf;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(?string $defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getNullable(): ?bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function getMustBeUnique(): ?bool
    {
        return $this->mustBeUnique;
    }

    public function setMustBeUnique(bool $mustBeUnique): self
    {
        $this->mustBeUnique = $mustBeUnique;

        return $this;
    }

    public function getReadOnly(): ?bool
    {
        return $this->readOnly;
    }

    public function setReadOnly(?bool $readOnly): self
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    public function getWriteOnly(): ?bool
    {
        return $this->writeOnly;
    }

    public function setWriteOnly(?bool $writeOnly): self
    {
        $this->writeOnly = $writeOnly;

        return $this;
    }

    public function getExample(): ?string
    {
        return $this->example;
    }

    public function setExample(?string $example): self
    {
        $this->example = $example;

        return $this;
    }

    public function getDeprecated(): ?bool
    {
        return $this->deprecated;
    }

    public function setDeprecated(?bool $deprecated): self
    {
        $this->deprecated = $deprecated;

        return $this;
    }

    public function getMinDate(): ?string
    {
        return $this->minDate;
    }

    public function setMinDate(?string $minDate): self
    {
        $this->minDate = $minDate;

        return $this;
    }

    public function getMaxDate(): ?string
    {
        return $this->maxDate;
    }

    public function setMaxDate(?string $maxDate): self
    {
        $this->maxDate = $maxDate;

        return $this;
    }


    public function getValidations(): ?array
    {
        //TODO: this list of validations is not complete!
        $validations = [];
        $validations['maximum']             = $this->getMaximum();
        $validations['exclusiveMaximum']    = $this->getExclusiveMaximum();
        $validations['minimum']             = $this->getMinimum();
        $validations['exclusiveMinimum']    = $this->getExclusiveMinimum();
        $validations['maxLength']           = $this->getMaxLength();
        $validations['minLength']           = $this->getMinLength();
        $validations['maxItems']            = $this->getMaxItems();
        $validations['minItems']            = $this->getMinItems();
        $validations['uniqueItems']         = $this->getUniqueItems();
        $validations['maxProperties']       = $this->getMaxProperties();
        $validations['minProperties']       = $this->getMinProperties();
        $validations['required']            = $this->getRequired();
        $validations['enum']                = $this->getEnum();
        $validations['allOf']               = $this->getAllOf();
        $validations['anyOf']               = $this->getAnyOf();
        $validations['oneOf']               = $this->getOneOf();
        $validations['defaultValue']        = $this->getDefaultValue();
        $validations['nullable']            = $this->getNullable();
        $validations['mustBeUnique']        = $this->getMustBeUnique();

        return $validations;
    }

    public function setValidations(?array $validations): self
    {
        //TODO: this list of validations is not complete!
        if(array_key_exists('maximum',$validations)){           $this->setMaximum($validations['maximum']);}
        if(array_key_exists('exclusiveMaximum',$validations)){  $this->setExclusiveMaximum($validations['exclusiveMaximum']);}
        if(array_key_exists('minimum',$validations)){           $this->setMinimum($validations['minimum']);}
        if(array_key_exists('exclusiveMinimum',$validations)){  $this->setExclusiveMinimum($validations['exclusiveMinimum']);}
        if(array_key_exists('maxLength',$validations)){         $this->setMaxLength($validations['maxLength']);}
        if(array_key_exists('minLength',$validations)){         $this->setMinLength($validations['minLength']);}
        if(array_key_exists('maxItems',$validations)){          $this->setMaxItems($validations['maxItems']);}
        if(array_key_exists('minItems',$validations)){          $this->setMinItems($validations['minItems']);}
        if(array_key_exists('uniqueItems',$validations)){       $this->setUniqueItems($validations['uniqueItems']);}
        if(array_key_exists('maxProperties',$validations)){     $this->setMaxProperties($validations['maxProperties']);}
        if(array_key_exists('minProperties',$validations)){     $this->setMinProperties($validations['minProperties']);}
        if(array_key_exists('required',$validations)){          $this->setRequired($validations['required']);}
        if(array_key_exists('enum',$validations)){              $this->setEnum($validations['enum']);}
        if(array_key_exists('allOf',$validations)){             $this->setAllOf($validations['allOf']);}
        if(array_key_exists('anyOf',$validations)){             $this->setAnyOf($validations['anyOf']);}
        if(array_key_exists('oneOf',$validations)){             $this->setOneOf($validations['oneOf']);}
        if(array_key_exists('defaultValue',$validations)){      $this->setDefaultValue($validations['defaultValue']);}
        if(array_key_exists('nullable',$validations)){          $this->setNullable($validations['nullable']);}
        if(array_key_exists('mustBeUnique',$validations)){      $this->setMustBeUnique($validations['mustBeUnique']);}

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(\DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    public function getPersistToGateway(): ?bool
    {
        return $this->persistToGateway;
    }

    public function setPersistToGateway(?bool $persistToGateway): self
    {
        $this->persistToGateway = $persistToGateway;

        return $this;
    }

    public function getSearchable(): ?bool
    {
        return $this->searchable;
    }

    public function setSearchable(?bool $searchable): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function getCascade(): ?bool
    {
        return $this->cascade;
    }

    public function setCascade(?bool $cascade): self
    {
        $this->cascade = $cascade;

        return $this;
    }

    public function getInversedBy(): ?bool
    {
        return $this->inversedBy;
    }

    public function setInversedBy(?bool $inversedBy): self
    {
        $this->inversedBy = $inversedBy;

        return $this;
    }



}