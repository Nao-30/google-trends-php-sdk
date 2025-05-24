# Google Trends PHP SDK - Project Mission

## Project Overview

This project aims to create a professional-grade PHP SDK named `gtrends-php-sdk` that serves as a client library for the Google Trends CLI API. The SDK is designed to integrate seamlessly with Laravel applications while maintaining compatibility with standalone PHP projects.

The project follows a structured approach with task breakdown files that guide implementation. Each component has been carefully planned to ensure maintainability, quality, and adherence to best practices.

## Repository Structure

```
gtrends-php-sdk/
├── src/                          # Main source code (PSR-4 autoloaded)
│   ├── Client.php                # Primary SDK client class
│   ├── Contracts/                # Interface definitions
│   ├── Exceptions/               # Custom exception classes
│   ├── Configuration/            # Configuration management
│   ├── Http/                     # HTTP layer abstractions
│   ├── Resources/                # API resource handlers
│   └── Laravel/                  # Laravel-specific integrations
├── tests/                        # Test suite
├── examples/                     # Usage examples
├── docs/                         # Documentation
├── .github/                      # GitHub workflows
├── config/                       # Default configurations
├── tasks/                        # Task tracking and breakdown
├── composer.json                 # Package manifest
└── [Various config files]        # PHPUnit, PHP_CodeSniffer, etc.
```

## Task Management System

The project uses a structured task management system in the `tasks/` directory:

1. **task_breakdown.md**: The master task list with implementation status
2. **SUMMARY.md**: Overview of project progress and what's been implemented
3. **task_logs.md**: Chronological log of work completed and next steps
4. **Numbered task files** (XX_task_name.md): Detailed specifications for each component

### Current Implementation Status

To determine the current state of the project and where to start:

1. First, read **tasks/SUMMARY.md** for a high-level overview
2. Check **tasks/task_logs.md** for the most recent work completed
3. Review **tasks/task_breakdown.md** to find uncompleted tasks (items marked with `[ ]`)
4. Locate the corresponding numbered task file for the next uncompleted task

## Implementation Guidelines

### Workflow Process

1. **Before starting implementation**:
   - Review the corresponding task file thoroughly
   - Understand dependencies and requirements
   - Read related source files if they exist

2. **During implementation**:
   - Follow the task file's implementation details
   - Adhere to acceptance criteria listed in the task file
   - Implement one component at a time
   - Write tests as you implement

3. **After completing a task**:
   - Update **task_breakdown.md** by changing `[ ]` to `[x]` for completed items
   - Add an entry to **task_logs.md** with:
     - Date of completion
     - Summary of what was implemented
     - References to files created or modified
     - Any challenges or notes
   - Update **SUMMARY.md** if significant progress was made

### Best Practices

- **Follow PSR-12** coding standards throughout the codebase
- **Document all code** with comprehensive PHPDoc comments
- **Write tests** for all implemented functionality
- **Commit messages** should be clear and reference task numbers
- **Create branches** for each major task implementation

## Git Workflow and Branch Management

### Branch Strategy

The project follows a structured branching strategy:

- **main**: Production-ready code (stable releases)
- **develop**: Integration branch for completed features
- **feature/task-name**: Individual task implementation branches
- **bugfix/issue-name**: Bug fix branches
- **release/vX.Y.Z**: Release preparation branches

### When to Create a New Branch

1. **Starting a new task**:
   - Always create a new feature branch when starting work on a new task from task_breakdown.md
   - Branch from `develop` using the naming convention `feature/task-name` (e.g., `feature/core-interfaces`)
   - Example: `git checkout develop && git checkout -b feature/core-interfaces`

2. **Continuing an existing task**:
   - Check if a branch already exists for the task you're working on:
     - `git branch --list "feature/*task-name*"`
   - If it exists and you're the same developer, check it out
   - If it exists but was created by someone else, coordinate before continuing work
   - If no branch exists, create a new one as described above

### Wrapping Up Work on a Branch

When you've completed a task:

1. **Ensure all tests pass**:
   - Run test suite: `composer test`
   - Check code style: `composer check-style`

2. **Update documentation**:
   - Mark the task as completed in task_breakdown.md
   - Add an entry to task_logs.md
   - Update SUMMARY.md if significant progress was made

3. **Commit all changes**:
   - Use clear commit messages referencing the task
   - Example: `git commit -m "Implement core interfaces (Task 01)"`

4. **Merge into develop branch**:
   - Push your feature branch: `git push origin feature/task-name`
   - Create a pull request from your feature branch to develop
   - After code review, merge the pull request
   - Example workflow:
     ```bash
     git checkout develop
     git pull origin develop
     git merge --no-ff feature/task-name
     git push origin develop
     ```

5. **Clean up**:
   - Delete the feature branch after successful merge
   - Example: `git branch -d feature/task-name`

### Preparing for the Next Work Session

Before ending your work session:

1. **Ensure develop branch is up to date**:
   - Merge your completed work to develop
   - Push all changes to remote

2. **Document your progress clearly**:
   - Update task_logs.md with what was completed and what's next
   - Note any in-progress work or challenges

3. **Leave clear next steps**:
   - Add specific notes in task_logs.md about where to pick up next

### Starting a New Work Session

When beginning a new work session:

1. **Sync with remote repository**:
   - `git fetch --all`
   - `git checkout develop`
   - `git pull origin develop`

2. **Review task tracking files**:
   - Check task_logs.md for the most recent work
   - Review task_breakdown.md for uncompleted tasks
   - Identify the next task to work on

3. **Create or checkout appropriate branch**:
   - For a new task: `git checkout -b feature/task-name`
   - For continuing work: `git checkout feature/task-name`

### Working with GitHub

- Always reference task numbers in pull request titles
- Include task acceptance criteria in the PR description
- Request code reviews from team members
- Address all review comments before merging
- Keep PRs focused on a single task for easier review

## What to Do and What Not to Do

### Do:

- ✅ Read task files completely before starting implementation
- ✅ Follow the implementation order in SUMMARY.md
- ✅ Update task tracking files after completing work
- ✅ Write comprehensive tests for all components
- ✅ Maintain consistent code style throughout
- ✅ Reference specific file paths in task logs
- ✅ Check dependencies before implementing a component
- ✅ Create a new branch for each major task
- ✅ Merge completed work to develop promptly
- ✅ Document branch status in task logs

### Don't:

- ❌ Skip ahead to later tasks before completing prerequisites
- ❌ Implement features not specified in task files
- ❌ Forget to update task tracking documentation
- ❌ Merge code without passing tests
- ❌ Change project structure without documentation
- ❌ Make assumptions about requirements without checking task files
- ❌ Work on multiple unrelated tasks simultaneously
- ❌ Leave branches hanging without merging or documenting status
- ❌ Work directly on develop or main branches
- ❌ Push broken code to shared branches

## Starting Implementation

To begin work on the next task:

1. Identify the next unmarked task in **tasks/task_breakdown.md**
2. Open the corresponding task file (e.g., **tasks/01_core_interfaces.md**)
3. Review the task description, implementation details, and acceptance criteria
4. Check for dependencies and ensure they're completed
5. Create a new feature branch for the task
6. Implement the required components
7. Write tests for your implementation
8. Update task tracking files
9. Create a pull request to merge into develop

## Understanding Task Files

Each task file follows this structure:

- **Task Description**: Overview of what needs to be implemented
- **Implementation Details**: Specific requirements for each component
- **Files to Create**: List of files that should be created or modified
- **Dependencies**: What this task requires to be completed first
- **Acceptance Criteria**: How to determine if the task is successfully completed

## Logging Work

When updating **task_logs.md**, always:

1. Add a new dated section (e.g., `## [2023-05-19] Core Interfaces Implementation`)
2. List specific files created or modified
3. Reference task file numbers
4. Note any challenges or important decisions
5. Update the "Next Steps" and "Pending Tasks" sections
6. Include branch status information (created, in progress, merged)

Example entry:

```markdown
## [2023-05-19] Core Interfaces Implementation
- Created interface definitions in src/Contracts/
  - Created ClientInterface.php
  - Created ConfigurationInterface.php
  - Created RequestBuilderInterface.php
  - Created ResponseHandlerInterface.php
- All interfaces follow PSR-12 standards
- Added comprehensive PHPDoc comments
- Referenced 01_core_interfaces.md requirements
- Branch status: feature/core-interfaces created and merged to develop

## Next Steps
- Begin exception hierarchy implementation (02_exception_hierarchy.md)
- Create new branch feature/exception-hierarchy
```

## Task Prioritization

The implementation order follows dependencies between components:

1. Core interfaces (foundation for all other components)
2. Exception hierarchy (required for error handling)
3. HTTP layer (for API communication)
4. Configuration management
5. Main client class
6. API endpoint implementations
7. Laravel integration
8. Testing infrastructure
9. Documentation and distribution
10. Maintenance planning
11. Open source preparation

Some tasks can be worked on in parallel (e.g., documentation and testing), but core components must follow the sequence.

## Final Notes

The project is designed as an open-source tool that will be published to Packagist. Maintain high quality, comprehensive documentation, and follow security best practices throughout implementation.

For any questions not covered in the task files, refer to the original `PM/PLAN.md` which contains comprehensive guidelines for the entire project.

Remember: The key to successful implementation is understanding the tasks thoroughly before coding, following the structured approach, and maintaining consistent documentation of progress. 