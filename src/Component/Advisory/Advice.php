<?php

declare(strict_types=1);

namespace Neu\Component\Advisory;

/**
 * Represents a piece of advice provided by the advisory system.
 */
final readonly class Advice
{
    /**
     * The category of the advice.
     *
     * @var AdviceCategory
     */
    public AdviceCategory $category;

    /**
     * A short message summarizing the advice.
     *
     * @var string
     */
    public string $message;

    /**
     * A detailed description of the advice.
     *
     * @var string
     */
    public string $description;

    /**
     * A suggested solution or action to address the advice.
     *
     * @var string
     */
    public string $solution;

    /**
     * Constructs a new instance of the Advice class.
     *
     * @param AdviceCategory $category The category of the advice.
     * @param string $message A short message summarizing the advice.
     * @param string $description A detailed description of the advice.
     * @param string $solution A suggested solution or action to address the advice.
     */
    public function __construct(AdviceCategory $category, string $message, string $description, string $solution)
    {
        $this->category = $category;
        $this->message = $message;
        $this->description = $description;
        $this->solution = $solution;
    }

    /**
     * Creates an advice instance for the security category.
     *
     * @param string $message The summary message.
     * @param string $description The detailed description.
     * @param string $solution The suggested solution.
     */
    public static function forSecurity(string $message, string $description, string $solution): self
    {
        return new self(AdviceCategory::Security, $message, $description, $solution);
    }

    /**
     * Creates an advice instance for the performance category.
     *
     * @param string $message The summary message.
     * @param string $description The detailed description.
     * @param string $solution The suggested solution.
     */
    public static function forPerformance(string $message, string $description, string $solution): self
    {
        return new self(AdviceCategory::Performance, $message, $description, $solution);
    }

    /**
     * Creates an advice instance for the maintainability category.
     *
     * @param string $message The summary message.
     * @param string $description The detailed description.
     * @param string $solution The suggested solution.
     */
    public static function forMaintainability(string $message, string $description, string $solution): self
    {
        return new self(AdviceCategory::Maintainability, $message, $description, $solution);
    }

    /**
     * Creates an advice instance for the usability category.
     *
     * @param string $message The summary message.
     * @param string $description The detailed description.
     * @param string $solution The suggested solution.
     */
    public static function forUsability(string $message, string $description, string $solution): self
    {
        return new self(AdviceCategory::Usability, $message, $description, $solution);
    }

    /**
     * Creates an advice instance for the accessibility category.
     *
     * @param string $message The summary message.
     * @param string $description The detailed description.
     * @param string $solution The suggested solution.
     */
    public static function forAccessibility(string $message, string $description, string $solution): self
    {
        return new self(AdviceCategory::Accessibility, $message, $description, $solution);
    }

    /**
     * Creates an advice instance for the other category.
     *
     * @param string $message The summary message.
     * @param string $description The detailed description.
     * @param string $solution The suggested solution.
     */
    public static function forOther(string $message, string $description, string $solution): self
    {
        return new self(AdviceCategory::Other, $message, $description, $solution);
    }
}
