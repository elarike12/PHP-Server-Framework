<?php

/**
 * TaskInterface class defines mandatory properties and methods for a task.
 *
 * copyright @ WereWolf Labs OÜ.
 */

namespace Framework\Task;

interface TaskInterface {
    /*
     * This method will return task name.
     */
    public function getName(): string;

    /*
     * This method will be called when task is executed.
     */
    public function execute();
}